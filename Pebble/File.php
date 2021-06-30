<?php declare(strict_types=1);

namespace Pebble;

class File
{

   /**
    * Recursively read all file in a dir except '.', '..'
    * From http://php.net/manual/en/function.scandir.php#110570
    */
   public static function dirToArray(string $dir) : array
   {

      $result = array();

      $cdir = scandir($dir);
      foreach ($cdir as $key => $value) {
         if (!in_array($value, array(".", ".."))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
               $result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value);
            } else {
               $result[] = $value;
            }
         }
      }
      return $result;
   }
}
