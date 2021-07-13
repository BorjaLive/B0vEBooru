<?php
  include "conn.php";
//var_dump($_POST);
  switch(sGet("action")){
    case "createArtist":
      echo createArtist(sGet("name"), $_FILES['pic']['tmp_name'], $_FILES['pic']['name']);
    break;
    case "deleteArtist":
      deleteArtist(sGet("id"));
    break;
    case "createCharacter":
      echo createCharacter(sGet("name"), $_FILES['pic']['tmp_name'], $_FILES['pic']['name']);
    break;
    case "deleteCharacter":
      deleteCharacter(sGet("id"));
    break;
    case "createPic":
      createPic(sGet("artist"), json_decode(sGet("characters"), true), $_FILES);
    break;
    case "deletePic":
      deletePic(sGet("id"));
    break;
    case "getPics":
      echo json_encode(getPics(sGet("artist"), sGet("characters")));
    break;
    case "getArtists":
      echo json_encode(getArtists());
    break;
    case "getCharacters":
      echo json_encode(getCharacters());
    break;
    default:
      echo "Action ".sGet("action")." not recognized.";
    break;
  }

  function sGet($name){
    return empty($_POST[$name])?null:$_POST[$name];
  }
?>
