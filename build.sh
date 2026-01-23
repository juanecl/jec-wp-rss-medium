#!/bin/bash

# Script para crear un archivo ZIP del plugin jec-medium
# Incrementa automáticamente la versión patch y actualiza los archivos necesarios

# Nombre del plugin
PLUGIN_NAME="jec-medium"
PLUGIN_SLUG="jec-wp-rss-medium"

# Obtener versión actual del header del plugin
# Buscar la línea que contiene solo "Version: X.Y.Z" en el header del plugin
CURRENT_VERSION=$(grep "^Version:" index.php | head -1 | sed 's/Version: *//' | tr -d ' \r\n')

# Validar que la versión fue encontrada y tiene el formato correcto
if [ -z "$CURRENT_VERSION" ] || ! [[ "$CURRENT_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "❌ Error: No se pudo leer la versión correctamente del archivo index.php"
    echo "Versión leída: '$CURRENT_VERSION'"
    echo "Se esperaba formato: X.Y.Z (ejemplo: 1.0.0)"
    exit 1
fi

# Incrementar versión patch (1.0.38 -> 1.0.39, 1.0.99 -> 1.1.0)
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR="${VERSION_PARTS[0]}"
MINOR="${VERSION_PARTS[1]}"
PATCH="${VERSION_PARTS[2]}"

# Si el patch llega a 99, incrementar minor y resetear patch a 0
if [ "$PATCH" -ge 99 ]; then
    NEW_MINOR=$((MINOR + 1))
    NEW_PATCH=0
    NEW_VERSION="${MAJOR}.${NEW_MINOR}.${NEW_PATCH}"
else
    NEW_PATCH=$((PATCH + 1))
    NEW_VERSION="${MAJOR}.${MINOR}.${NEW_PATCH}"
fi

echo "=========================================="
echo "Actualizando versión de ${PLUGIN_NAME}"
echo "Versión actual: ${CURRENT_VERSION}"
echo "Nueva versión: ${NEW_VERSION}"
echo "=========================================="

# Fecha actual
CURRENT_DATE=$(date +%Y-%m-%d)

# Actualizar index.php (header) - usar sed compatible con macOS/BSD
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS (BSD sed)
    sed -i '' "s/^Version: ${CURRENT_VERSION}/Version: ${NEW_VERSION}/" index.php
else
    # Linux (GNU sed)
    sed -i "s/^Version: ${CURRENT_VERSION}/Version: ${NEW_VERSION}/" index.php
fi

# Actualizar index.php (constante)
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s/JEC_MEDIUM_VERSION', '${CURRENT_VERSION}'/JEC_MEDIUM_VERSION', '${NEW_VERSION}'/" index.php
else
    sed -i "s/JEC_MEDIUM_VERSION', '${CURRENT_VERSION}'/JEC_MEDIUM_VERSION', '${NEW_VERSION}'/" index.php
fi

# Actualizar readme.txt
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s/Stable tag: ${CURRENT_VERSION}/Stable tag: ${NEW_VERSION}/" readme.txt
else
    sed -i "s/Stable tag: ${CURRENT_VERSION}/Stable tag: ${NEW_VERSION}/" readme.txt
fi

# Actualizar CHANGELOG.md (agregar nueva entrada al inicio)
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS requiere sintaxis diferente para insertar antes de una línea
    sed -i '' "/## \[${CURRENT_VERSION}\]/i\\
## [${NEW_VERSION}] - ${CURRENT_DATE}\\
\\
### Changed\\
\\
- Build automático - versión incrementada\\
\\
" CHANGELOG.md
else
    # Linux
    sed -i "/## \[${CURRENT_VERSION}\]/i\\
## [${NEW_VERSION}] - ${CURRENT_DATE}\\
\\
### Changed\\
\\
- Build automático - versión incrementada\\
\\
" CHANGELOG.md
fi

echo "✓ Archivos actualizados a versión ${NEW_VERSION}"
echo ""

# Compilar archivos de traducción .mo desde .po (para incluir en el build)
echo "Compilando archivos de traducción..."
if command -v msgfmt &> /dev/null; then
    for po_file in languages/*.po; do
        if [ -f "$po_file" ]; then
            mo_file="${po_file%.po}.mo"
            msgfmt "$po_file" -o "$mo_file"
            echo "✓ Compilado: $(basename $mo_file)"
        fi
    done
    echo "✓ Archivos de traducción compilados"
else
    echo "⚠ msgfmt no está disponible. Instala gettext para compilar traducciones."
    echo "  macOS: brew install gettext && brew link gettext"
    echo "  Continuando sin compilar traducciones..."
fi
echo ""

# Crear directorio de build
BUILD_DIR="$HOME/.build"
mkdir -p "$BUILD_DIR"

# Limpiar archivos ZIP anteriores en el directorio de build
echo "Limpiando builds anteriores..."
rm -f "$BUILD_DIR"/${PLUGIN_NAME}-*.zip
echo "✓ Builds anteriores eliminados"
echo ""

# Nombre del archivo de salida
OUTPUT_FILE="${PLUGIN_NAME}-${NEW_VERSION}.zip"

# Directorio temporal para preparar el plugin
TEMP_DIR="/tmp/${PLUGIN_NAME}-build"

echo "=========================================="
echo "Construyendo ${PLUGIN_NAME} v${NEW_VERSION}"
echo "=========================================="

# Limpiar directorio temporal si existe
if [ -d "$TEMP_DIR" ]; then
    rm -rf "$TEMP_DIR"
fi

# Crear directorio temporal
mkdir -p "$TEMP_DIR/$PLUGIN_SLUG"

# Copiar archivos del plugin
echo "Copiando archivos del plugin..."
rsync -av \
    --exclude='.git' \
    --exclude='.gitignore' \
    --exclude='.DS_Store' \
    --exclude='*.md' \
    --exclude='*.json' \
    --exclude='*.log' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.vscode' \
    --exclude='.idea' \
    --exclude='*.sh' \
    --exclude='tests' \
    --exclude='*.zip' \
    --exclude='.editorconfig' \
    --exclude='.phpcs.xml' \
    --exclude='phpunit.xml' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='.history' \
    --exclude='.trunk' \
    --exclude='build' \
    ./ "$TEMP_DIR/$PLUGIN_SLUG/"

echo "✓ Archivos copiados"
echo ""

# Minificar archivos CSS y JS en el directorio temporal (sin tocar originales)
echo "Optimizando archivos para producción..."
OPTIMIZED=false

# Verificar si las herramientas de minificación están disponibles
if command -v npx &> /dev/null; then
    # Verificar si package.json existe, si no, crear configuración temporal
    if [ ! -f "package.json" ]; then
        echo "Instalando herramientas de minificación y ofuscación..."
        npm install --prefix /tmp --silent terser clean-css-cli javascript-obfuscator 2>/dev/null
        NPM_PREFIX="/tmp"
    else
        NPM_PREFIX="."
    fi
    
    # Minificar CSS en el directorio temporal
    if [ -d "$TEMP_DIR/$PLUGIN_SLUG/assets/css" ]; then
        echo "Minificando archivos CSS..."
        for css_file in "$TEMP_DIR/$PLUGIN_SLUG/assets/css"/*.css; do
            if [ -f "$css_file" ]; then
                filename=$(basename "$css_file")
                ORIGINAL_SIZE=$(wc -c < "$css_file" | tr -d ' ')
                
                # Crear archivo temporal para minificación
                TEMP_CSS="/tmp/temp_${filename}"
                npx --prefix "$NPM_PREFIX" cleancss -o "$TEMP_CSS" "$css_file" 2>/dev/null
                
                if [ $? -eq 0 ] && [ -f "$TEMP_CSS" ]; then
                    MINIFIED_SIZE=$(wc -c < "$TEMP_CSS" | tr -d ' ')
                    SAVED=$((ORIGINAL_SIZE - MINIFIED_SIZE))
                    PERCENT=$((SAVED * 100 / ORIGINAL_SIZE))
                    
                    # Reemplazar archivo en el directorio temporal del build
                    mv "$TEMP_CSS" "$css_file"
                    echo "  ✓ $filename (reducido ${PERCENT}%)"
                    OPTIMIZED=true
                else
                    rm -f "$TEMP_CSS"
                fi
            fi
        done
    fi
    
    # Minificar JS en el directorio temporal
    if [ -d "$TEMP_DIR/$PLUGIN_SLUG/assets/js" ]; then
        echo "Minificando archivos JavaScript..."
        for js_file in "$TEMP_DIR/$PLUGIN_SLUG/assets/js"/*.js; do
            if [ -f "$js_file" ]; then
                filename=$(basename "$js_file")
                ORIGINAL_SIZE=$(wc -c < "$js_file" | tr -d ' ')
                
                # Crear archivo temporal para minificación y ofuscación extrema
                TEMP_JS="/tmp/temp_${filename}"
                TEMP_JS_OBF="/tmp/temp_obf_${filename}"
                
                # Paso 1: Minificar y ofuscar con terser (más agresivo)
                npx --prefix "$NPM_PREFIX" terser "$js_file" -o "$TEMP_JS" \
                    --compress passes=3,dead_code=true,drop_console=true,drop_debugger=true,conditionals=true,evaluate=true,booleans=true,loops=true,unused=true,hoist_funs=true,hoist_vars=true,if_return=true,join_vars=true,reduce_vars=true,side_effects=true \
                    --mangle toplevel=true,eval=true,reserved=['jQuery','$','wp','jecMediumData'] \
                    --format comments=false,beautify=false,ascii_only=true 2>/dev/null
                
                # Paso 2: Ofuscar aún más con javascript-obfuscator
                if [ $? -eq 0 ] && [ -f "$TEMP_JS" ]; then
                    npx --prefix "$NPM_PREFIX" javascript-obfuscator "$TEMP_JS" \
                        --output "$TEMP_JS_OBF" \
                        --compact true \
                        --control-flow-flattening true \
                        --control-flow-flattening-threshold 0.75 \
                        --dead-code-injection true \
                        --dead-code-injection-threshold 0.4 \
                        --debug-protection false \
                        --disable-console-output true \
                        --identifier-names-generator hexadecimal \
                        --log false \
                        --numbers-to-expressions true \
                        --rename-globals false \
                        --rotate-string-array true \
                        --self-defending true \
                        --shuffle-string-array true \
                        --simplify true \
                        --split-strings true \
                        --split-strings-chunk-length 10 \
                        --string-array true \
                        --string-array-calls-transform true \
                        --string-array-encoding rc4 \
                        --string-array-index-shift true \
                        --string-array-rotate true \
                        --string-array-shuffle true \
                        --string-array-wrappers-count 2 \
                        --string-array-wrappers-chained-calls true \
                        --string-array-wrappers-parameters-max-count 4 \
                        --string-array-wrappers-type function \
                        --string-array-threshold 0.75 \
                        --transform-object-keys true \
                        --unicode-escape-sequence true 2>/dev/null
                    
                    if [ $? -eq 0 ] && [ -f "$TEMP_JS_OBF" ]; then
                        FINAL_FILE="$TEMP_JS_OBF"
                    else
                        # Si falla javascript-obfuscator, usar solo terser
                        FINAL_FILE="$TEMP_JS"
                    fi
                else
                    FINAL_FILE=""
                fi
                
                if [ -n "$FINAL_FILE" ] && [ -f "$FINAL_FILE" ]; then
                    MINIFIED_SIZE=$(wc -c < "$FINAL_FILE" | tr -d ' ')
                    SAVED=$((ORIGINAL_SIZE - MINIFIED_SIZE))
                    PERCENT=$((SAVED * 100 / ORIGINAL_SIZE))
                    
                    # Reemplazar archivo en el directorio temporal del build
                    mv "$FINAL_FILE" "$js_file"
                    echo "  ✓ $filename (reducido ${PERCENT}%, ofuscado)"
                    OPTIMIZED=true
                    
                    # Limpiar archivos temporales
                    rm -f "$TEMP_JS" "$TEMP_JS_OBF"
                else
                    rm -f "$TEMP_JS" "$TEMP_JS_OBF"
                fi
            fi
        done
    fi
    
    if [ "$OPTIMIZED" = true ]; then
        echo "✓ Archivos optimizados y ofuscados en el paquete (originales sin modificar)"
    fi
    
    # Limpiar configuración temporal si se creó
    if [ "$NPM_PREFIX" = "/tmp" ]; then
        rm -f /tmp/package-build.json
    fi
else
    echo "⚠ npm no está disponible. Instala Node.js para optimizar CSS/JS."
    echo "  macOS: brew install node"
    echo "  Continuando sin optimización..."
fi
echo ""

# Crear el archivo ZIP
echo "Creando archivo ZIP..."
cd "$TEMP_DIR"
zip -r "$OUTPUT_FILE" "$PLUGIN_SLUG" -q

# Guardar el directorio original para volver
ORIGINAL_DIR="$OLDPWD"

# Mover el ZIP al directorio de build
mv "$OUTPUT_FILE" "$BUILD_DIR/"

# Volver al directorio original
cd "$ORIGINAL_DIR"

# Limpiar directorio temporal
rm -rf "$TEMP_DIR"

echo "=========================================="
echo "✓ Plugin empaquetado exitosamente"
echo "Versión: ${NEW_VERSION}"
echo "Ubicación: $BUILD_DIR/$OUTPUT_FILE"
echo "Tamaño: $(du -h "$BUILD_DIR/$OUTPUT_FILE" | cut -f1)"
echo "=========================================="
echo ""
echo "Recuerda hacer commit de los cambios de versión:"
echo "  git add index.php readme.txt CHANGELOG.md"
echo "  git commit -m \"Bump version to ${NEW_VERSION}\""
echo "=========================================="
