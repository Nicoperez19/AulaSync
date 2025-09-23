<?php

// Comando para probar la carga de espacios en Tinker
// Ejecutar: php artisan tinker

echo "=== PRUEBA DE CARGA DE ESPACIOS ===\n\n";

echo "1. Verificar espacios en la base de datos:\n";
echo "\$espacios = App\\Models\\Espacio::all();\n";
echo "echo 'Total espacios: ' . \$espacios->count();\n";
echo "\$espacios->each(function(\$espacio) {\n";
echo "    echo \"ID: {\$espacio->id_espacio}, Nombre: {\$espacio->nombre_espacio}, Estado: {\$espacio->estado}\\n\";\n";
echo "});\n\n";

echo "2. Verificar espacios con estado activo:\n";
echo "\$espaciosActivos = App\\Models\\Espacio::where('estado', 'activo')->get();\n";
echo "echo 'Espacios activos: ' . \$espaciosActivos->count();\n\n";

echo "3. Verificar espacios sin filtro de estado:\n";
echo "\$todoEspacios = App\\Models\\Espacio::orderBy('nombre_espacio')->get();\n";
echo "echo 'Todos los espacios: ' . \$todoEspacios->count();\n";
echo "\$todoEspacios->take(5)->each(function(\$espacio) {\n";
echo "    echo \"ID: {\$espacio->id_espacio}, Nombre: {\$espacio->nombre_espacio}\\n\";\n";
echo "});\n\n";

echo "4. Probar el método de reagendamiento:\n";
echo "\$clase = App\\Models\\ClaseNoRealizada::first();\n";
echo "if (\$clase) {\n";
echo "    echo 'Probando con clase ID: ' . \$clase->id;\n";
echo "    // Simular la lógica del showReagendarModal\n";
echo "    \$espacios = App\\Models\\Espacio::select('id_espacio', 'nombre_espacio', 'tipo_espacio')\n";
echo "        ->where('estado', 'activo')\n";
echo "        ->orWhereNull('estado')\n";
echo "        ->orderBy('nombre_espacio')\n";
echo "        ->get();\n";
echo "    \n";
echo "    if (\$espacios->isEmpty()) {\n";
echo "        \$espacios = App\\Models\\Espacio::select('id_espacio', 'nombre_espacio', 'tipo_espacio')\n";
echo "            ->orderBy('nombre_espacio')\n";
echo "            ->get();\n";
echo "    }\n";
echo "    \n";
echo "    echo 'Espacios para reagendar: ' . \$espacios->count();\n";
echo "    \$espacios->take(3)->each(function(\$espacio) {\n";
echo "        echo \"- ID: {\$espacio->id_espacio}, Nombre: \" . (\$espacio->nombre_espacio ?? 'Sin nombre') . \"\\n\";\n";
echo "    });\n";
echo "}\n\n";

echo "5. Verificar la estructura de la tabla espacios:\n";
echo "echo 'Columnas de la tabla espacios:';\n";
echo "\$columns = DB::select('DESCRIBE espacios');\n";
echo "foreach (\$columns as \$column) {\n";
echo "    echo \"- {\$column->Field} ({\$column->Type})\\n\";\n";
echo "}\n\n";

echo "=== NOTAS ===\n";
echo "- Si no aparecen espacios, verificar el campo 'estado' en la tabla\n";
echo "- Algunos espacios podrían no tener nombre_espacio definido\n";
echo "- El JavaScript agregado incluye console.log para debug\n";
echo "- Abrir DevTools para ver los datos de espacios en la consola\n\n";

?>