<?php
  include "constants.php";

  function conn(){
    return new Mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
  }

  function processImage($source, $type){
    $type =  pathinfo($type, PATHINFO_EXTENSION);
    echo "type: ".$type."|";
    switch($type){
  		case "jpg":
  			return imagecreatefromjpeg($source);
  		break;
  		case "png":
  			return imagecreatefrompng($source);
  		break;
  		case "gif":
  			return imagecreatefromgif($source);
  		break;
  	}
  }
  function generateThumb($source, $dest, $type){
			list($ancho, $alto, $tipo, $atributo) = getimagesize($source);
			if($ancho > $alto){
				$nuevo_ancho = THUMB_SIZE;
				$nuevo_alto = $alto*(THUMB_SIZE/$ancho);
			}else{
				$nuevo_alto = THUMB_SIZE;
				$nuevo_ancho = $ancho*(THUMB_SIZE/$alto);
			}
			$thumb = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
			$origen = processImage($source, $type);
			imagecopyresized($thumb, $origen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
			imagejpeg($thumb, $dest, 75);
  }
  function renderPic($source, $dest, $type){
			imagejpeg(processImage($source, $type), $dest, 75);
  }

  function createArtist($name, $img, $type){
    $link = conn();
    $stmt = $link->prepare("INSERT INTO `artista` (`nombre`) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt -> close();
    $id = $link->insert_id;
    $link->close();
    generateThumb($img, "DATA/ARTIST/".$id.".jpg", $type);
    return $id;
  }
  function deleteArtist($id){
    $link = conn();
    $stmt = $link->prepare("DELETE FROM `artista` WHERE `id` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt -> close();
    $link->close();
  }
  function createCharacter($name, $img, $type){
    $link = conn();
    $stmt = $link->prepare("INSERT INTO `personaje` (`nombre`) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt -> close();
    $id = $link->insert_id;
    $link->close();
    generateThumb($img, "DATA/CHARACTER/".$id.".jpg", $type);
    return $id;
  }
  function deleteCharacter($id){
    $link = conn();
    $stmt = $link->prepare("DELETE FROM `personaje` WHERE `id` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt -> close();
    $link->close();
  }
  function createPic($artist, $characters, $files){
    $link = conn();
    $n = count($files['pic']['name']);
    for($i = 0; $i < $n; $i++){
      $img = $files['pic']['tmp_name'][$i];
      $type = $files['pic']['name'][$i];

      $stmt = $link->prepare("INSERT INTO `imagen` (`artista`) VALUES (?)");
      $stmt->bind_param("i", $artist);
      echo $artist."|";
      $stmt->execute();
      $stmt -> close();
      $id = $link->insert_id;
      $stmt = $link->prepare("INSERT INTO `aparece` (`imagen`, `personaje`) VALUES (?, ?)");
      foreach($characters as $character){
        $stmt->bind_param("ii", $id, $character);
        $stmt->execute();
      }
      $stmt -> close();

      generateThumb($img, "DATA/THUMB/".$id.".jpg", $type);
      renderPic($img, "DATA/PIC/".$id.".jpg", $type);
    }
    $link->close();
  }
  function deletePic($id){
    $link = conn();
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt -> close();
    $link->close();
  }

  function getPics($artist, $characters){
    $link = conn();
    if($artist === null && $characters === null){
      $stmt = $link->prepare("SELECT `id` FROM `imagen` ORDER BY `ts` DESC");
    }else if($artist !== null && $characters === null){
      $stmt = $link->prepare("SELECT `id` FROM `imagen` WHERE `artista` = ? ORDER BY `ts` DESC");
      $stmt->bind_param("i", $artist);
    }else if($characters !== null && $artist === null){
      $set = "";
      $ies = "";
      for($i = 0; $i < count($characters); $i++){
        $set .= "?";
        if($i != count($characters)-1) $set .= ", ";
        $ies .= "i";
      }
      $stmt = $link->prepare("SELECT `id` FROM `imagen` WHERE EXISTS (SELECT * FROM `aparece` WHERE `imagen` = `id` and `personaje` in ($set)) ORDER BY `ts` DESC");
      $stmt->bind_param($ies, ...$characters);
    }else{
      $set = "";
      $ies = "";
      for($i = 0; $i < count($characters); $i++){
        $set .= "?";
        if($i != count($characters)-1) $set .= ", ";
        $ies .= "i";
      }
      $stmt = $link->prepare("SELECT `id` FROM `imagen` WHERE `artista` = ? AND EXISTS (SELECT * FROM `aparece` WHERE `imagen` = `id` and `personaje` in ($set)) ORDER BY `ts` DESC");
      $stmt->bind_param("i".$ies, $artist, ...$characters);
    }
    $stmt->execute();
    $stmt->bind_result($pic);

    $pics = [];
    while ($stmt->fetch()) {
        $pics[] = [
          "id" => $pic,
          "url" => EXTERNAL_URL."DATA/PIC/".$pic.".jpg",
          "thumb" => EXTERNAL_URL."DATA/THUMB/".$pic.".jpg"
        ];
    }
    $stmt -> close();
    $link->close();

    return $pics;
  }
  function getArtists(){
    $link = conn();
    $stmt = $link->prepare("SELECT `id`, `nombre` FROM `artista`");
    $stmt->execute();
    $stmt->bind_result($id, $name);

    $artists = [];
    while ($stmt->fetch()) {
        $artists[] = [
          "id" => $id,
          "name" => $name,
          "thumb" => EXTERNAL_URL."DATA/ARTIST/".$id.".jpg"
        ];
    }
    $stmt -> close();
    $link->close();

    return $artists;
  }
  function getCharacters(){
    $link = conn();
    $stmt = $link->prepare("SELECT `id`, `nombre` FROM `personaje`");
    $stmt->execute();
    $stmt->bind_result($id, $name);

    $characters = [];
    while ($stmt->fetch()) {
        $characters[] = [
          "id" => $id,
          "name" => $name,
          "thumb" => EXTERNAL_URL."DATA/CHARACTER/".$id.".jpg"
        ];
    }
    $stmt -> close();
    $link->close();

    return $characters;
  }

?>
