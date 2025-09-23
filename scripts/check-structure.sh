#!/bin/bash

# Script para validar la estructura del plugin CHASIDE
# Uso: ./scripts/check-structure.sh

echo "🔍 Validando estructura del plugin CHASIDE..."
echo "================================================"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Contador de errores
errors=0

# Archivos requeridos
required_files=(
    "version.php"
    "block_chaside.php" 
    "db/install.xml"
    "lang/en/block_chaside.php"
    "lang/es/block_chaside.php"
    "view.php"
    "view_results.php"
    "classes/facade.php"
    "README.md"
    "CHANGELOG.md"
)

echo "📁 Verificando archivos requeridos..."
for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "✅ ${GREEN}$file${NC}"
    else
        echo -e "❌ ${RED}$file (FALTANTE)${NC}"
        ((errors++))
    fi
done

echo ""
echo "🔢 Validando formato de versión..."
if [ -f "version.php" ]; then
    VERSION=$(grep -oP "(?<=\\\$plugin->release = ')[^']*" version.php)
    if [[ $VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo -e "✅ ${GREEN}Versión válida: $VERSION${NC}"
    else
        echo -e "❌ ${RED}Formato de versión inválido: $VERSION${NC}"
        echo -e "   ${YELLOW}Esperado: X.Y.Z (ejemplo: 1.2.0)${NC}"
        ((errors++))
    fi
else
    echo -e "❌ ${RED}version.php no encontrado${NC}"
    ((errors++))
fi

echo ""
echo "🔍 Validando sintaxis PHP..."
php_errors=0
while IFS= read -r -d '' file; do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "❌ ${RED}Error de sintaxis en: $file${NC}"
        ((php_errors++))
        ((errors++))
    fi
done < <(find . -name "*.php" -print0)

if [ $php_errors -eq 0 ]; then
    echo -e "✅ ${GREEN}Toda la sintaxis PHP es válida${NC}"
fi

echo ""
echo "📝 Verificando changelog..."
if [ -f "CHANGELOG.md" ]; then
    if [ -f "version.php" ]; then
        VERSION=$(grep -oP "(?<=\\\$plugin->release = ')[^']*" version.php)
        if grep -q "\[$VERSION\]" CHANGELOG.md; then
            echo -e "✅ ${GREEN}Versión $VERSION documentada en changelog${NC}"
        else
            echo -e "⚠️  ${YELLOW}Versión $VERSION no encontrada en changelog${NC}"
        fi
    fi
else
    echo -e "⚠️  ${YELLOW}CHANGELOG.md no encontrado${NC}"
fi

echo ""
echo "🎯 Verificando directorios de idiomas..."
lang_dirs=("lang/en" "lang/es")
for dir in "${lang_dirs[@]}"; do
    if [ -d "$dir" ]; then
        if [ -f "$dir/block_chaside.php" ]; then
            echo -e "✅ ${GREEN}$dir/block_chaside.php${NC}"
        else
            echo -e "❌ ${RED}$dir/block_chaside.php (FALTANTE)${NC}"
            ((errors++))
        fi
    else
        echo -e "❌ ${RED}Directorio $dir (FALTANTE)${NC}"
        ((errors++))
    fi
done

echo ""
echo "================================================"
if [ $errors -eq 0 ]; then
    echo -e "🎉 ${GREEN}¡Validación completada exitosamente!${NC}"
    echo -e "   ${GREEN}Estructura del plugin es correcta${NC}"
    exit 0
else
    echo -e "💥 ${RED}Validación falló con $errors errores${NC}"
    echo -e "   ${RED}Por favor, corrige los errores antes de continuar${NC}"
    exit 1
fi