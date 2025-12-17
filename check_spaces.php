$espacios = DB::table('espacios')->where('id_espacio', 'like', 'CH%')->get();
echo "Total CH spaces: " . count($espacios) . "\n";
foreach($espacios as $e) {
  echo $e->id_espacio . " - " . $e->nombre_espacio . "\n";
}
