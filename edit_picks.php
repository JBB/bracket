<?php

$username = htmlspecialchars($_POST["username"]);
$pw = htmlspecialchars($_POST["password"]);

//if file doesn't exist, add message before displaying home page that says
//"no such entry to edit"
//if pw doesen't match redirect to a page that has that message and points
//them back to standings
$now = getdate();
if 
(
  ($now[mday] > 15) || ($now[mon] != 3) ||
  (($now[mday] == 15) && ($now[hours] > 15))
)
{
  header("Location: http://www.empyre.com/ncaa/toolate.htm");
  exit;
}


$fileData;
$path_to_file = "./out12/$username.out";
if (file_exists($path_to_file)) {
  $file = fopen($path_to_file,"r");
  while(!feof($file)) {
    list($name,$value) = split("\t",fgets($file));
    $fileData[$name] = $value;
  }
  fclose($file);
} else {
  header("Location: http://www.empyre.com/ncaa/invalid.htm");
  exit;
}

$fileData['username'] = trim($fileData['username']);
$fileData['password'] = trim($fileData['password']);
if ($pw != $fileData['password']) {
  header("Location: http://www.empyre.com/ncaa/editpw.htm");
  exit;
}

//Do some logging
$file = fopen("ncaa.log","a");
$stringData = $fileData['username'] . " edited picks at " . date(DATE_RFC822) . "\n";
fwrite($file, $stringData);
fclose($file);

//write a temp file to tell processor.cgi it's OK to overwrite
$file = fopen("./out12/" . $fileData['username'] . ".tmp","w");
fwrite($file, "green light");
fclose($file);

function __json_encode( $data ) {            
    if( is_array($data) || is_object($data) ) { 
        $islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) ); 
        
        if( $islist ) { 
            $json = '[' . implode(',', array_map('__json_encode', $data) ) . ']'; 
        } else { 
            $items = Array(); 
            foreach( $data as $key => $value ) { 
                $items[] = __json_encode("$key") . ':' . __json_encode($value); 
            } 
            $json = '{' . implode(',', $items) . '}'; 
        } 
    } elseif( is_string($data) ) { 
        # Escape non-printable or Non-ASCII characters. 
        # I also put the \\ character first, as suggested in comments on the 'addclashes' page. 
        $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"'; 
        $json    = ''; 
        $len    = strlen($string); 
        # Convert UTF-8 to Hexadecimal Codepoints. 
        for( $i = 0; $i < $len; $i++ ) { 
            
            $char = $string[$i]; 
            $c1 = ord($char); 
            
            # Single byte; 
            if( $c1 <128 ) { 
                $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1); 
                continue; 
            } 
            
            # Double byte 
            $c2 = ord($string[++$i]); 
            if ( ($c1 & 32) === 0 ) { 
                $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128); 
                continue; 
            } 
            
            # Triple 
            $c3 = ord($string[++$i]); 
            if( ($c1 & 16) === 0 ) { 
                $json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128)); 
                continue; 
            } 
                
            # Quadruple 
            $c4 = ord($string[++$i]); 
            if( ($c1 & 8 ) === 0 ) { 
                $u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1; 
            
                $w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3); 
                $w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128); 
                $json .= sprintf("\\u%04x\\u%04x", $w1, $w2); 
            } 
        } 
    } else { 
        # int, floats, bools, null 
        $json = strtolower(var_export( $data, true )); 
    } 
    return $json; 
} 

?>
<html>
<head>

<title>NCAA Tournament Bracket</title>
<link rel="stylesheet" href="css/style.css">

<script>
var json_picks = <?php echo __json_encode($fileData); ?>;
var finalistHalf = null;
var championHalf = null;

function rePopulate() {
  var teams = document.querySelectorAll('.team-box');
  var theForm = document.getElementById('post-picks');
  theForm.username.value = json_picks['username'];
  theForm.password.value = json_picks['password'];

    
  [].forEach.call(teams, function(team) {
    document.getElementById(team.id).innerHTML = json_picks[team.id];
  });
     
  var check0 = document.getElementById('ff1r2g1t1');
  var check1 = document.getElementById('ff1r2g1t2');
  var check2 = document.getElementById('ff1r1g1t1');
  var check3 = document.getElementById('ff1r1g1t2');
  if (check0.innerHTML.length > 1) {
    if ((check0.innerHTML == check2.innerHTML) || 
        (check0.innerHTML == check3.innerHTML)) {
      championHalf = 1;
    } else {
      championHalf = 2;
    }
  }
  if (check1.innerHTML.length > 1) {
    if ((check1.innerHTML == check2.innerHTML) || 
        (check1.innerHTML == check3.innerHTML)) {
      finalistHalf = 1;
    } else {
      finalistHalf = 2;
    }
  }
}
</script>
</head>
<body onload="rePopulate();">

<section class="main">
  <header>
    <h1>Hello <?= $fileData['username'] ?>, you may edit your picks for the empyre.com NCAA pool</h1>
  </header>
  <div class="main">
    <div class="menu-bar">
      <button type="button" class="menu-button" id="clearAll" onclick="handleClear();">Clear</button>
      <button type="button" class="menu-button" id="autoFill" onclick="handleAutofill();">Auto Fill</button>
    </div>
    <div class="column-titles">
      <div class="one-column">1st Round</div>
      <div class="one-column">2nd Round</div>
      <div class="one-column">Sweet 16</div>
      <div class="one-column">Great 8</div>
      <div class="one-column">Final Four</div>
      <div class="one-column">Championship</div>
    </div>

    <!--ROUND 1 GAMES-->
    <div class="one-column">
      <div class="one-box-round1">
        <!--quarter1, round, game1, team1-->
        <span class="seed">1</span>
        <span draggable="true" class="team-box" id="q1r1g1t1">North Carolina</span>
        <hr class="team-box-hr">
        <span class="seed">16</span>
        <span draggable="true" class="team-box" id="q1r1g1t2">Richmond</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">8</span>
        <span draggable="true" class="team-box" id="q1r1g2t1">Kentucky</span>
        <hr class="team-box-hr">
        <span class="seed">9</span>
        <span draggable="true" class="team-box" id="q1r1g2t2">BYU</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">12</span>
        <span draggable="true" class="team-box" id="q1r1g3t1">UCLA</span>
        <hr class="team-box-hr">
        <span class="seed">5</span>
        <span draggable="true" class="team-box" id="q1r1g3t2">USC</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">13</span>
        <span draggable="true" class="team-box" id="q1r1g4t1">Stanford</span>
        <hr class="team-box-hr">
        <span class="seed">4</span>
        <span draggable="true" class="team-box" id="q1r1g4t2">Arizona</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">3</span>
        <span draggable="true" class="team-box" id="q1r1g5t1">Indiana</span>
        <hr class="team-box-hr">
        <span class="seed">14</span>
        <span draggable="true" class="team-box" id="q1r1g5t2">Michigan St.</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">6</span>
        <span draggable="true" class="team-box" id="q1r1g6t1">Ohio St.</span>
        <hr class="team-box-hr">
        <span class="seed">11</span>
        <span draggable="true" class="team-box" id="q1r1g6t2">Utah</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">10</span>
        <span draggable="true" class="team-box" id="q1r1g7t1">Colorado</span>
        <hr class="team-box-hr">
        <span class="seed">7</span>
        <span draggable="true" class="team-box" id="q1r1g7t2">Texas</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">15</span>
        <span draggable="true" class="team-box" id="q1r1g8t1">Fordham</span>
        <hr class="team-box-hr">
        <span class="seed">2</span>
        <span draggable="true" class="team-box" id="q1r1g8t2">Duke</span>
      </div>
      <hr class="quarter-break">
      <div class="one-box-round1">
        <span class="seed">1</span>
        <span draggable="true" class="team-box" id="q2r1g1t1">N Carolina</span>
        <hr class="team-box-hr">
        <span class="seed">16</span>
        <span draggable="true" class="team-box" id="q2r1g1t2">Rich</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">8</span>
        <span draggable="true" class="team-box" id="q2r1g2t1">Kent</span>
        <hr class="team-box-hr">
        <span class="seed">9</span>
        <span draggable="true" class="team-box" id="q2r1g2t2">BY</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">12</span>
        <span draggable="true" class="team-box" id="q2r1g3t1">UCL</span>
        <hr class="team-box-hr">
        <span class="seed">5</span>
        <span draggable="true" class="team-box" id="q2r1g3t2">SC</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">13</span>
        <span draggable="true" class="team-box" id="q2r1g4t1">Stan</span>
        <hr class="team-box-hr">
        <span class="seed">4</span>
        <span draggable="true" class="team-box" id="q2r1g4t2">Ariz</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">3</span>
        <span draggable="true" class="team-box" id="q2r1g5t1">Ind</span>
        <hr class="team-box-hr">
        <span class="seed">14</span>
        <span draggable="true" class="team-box" id="q2r1g5t2">Michigan</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">6</span>
        <span draggable="true" class="team-box" id="q2r1g6t1">Ohio</span>
        <hr class="team-box-hr">
        <span class="seed">11</span>
        <span draggable="true" class="team-box" id="q2r1g6t2">Utah St.</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">10</span>
        <span draggable="true" class="team-box" id="q2r1g7t1">Col</span>
        <hr class="team-box-hr">
        <span class="seed">7</span>
        <span draggable="true" class="team-box" id="q2r1g7t2">Tex</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">15</span>
        <span draggable="true" class="team-box" id="q2r1g8t1">Ford</span>
        <hr class="team-box-hr">
        <span class="seed">2</span>
        <span draggable="true" class="team-box" id="q2r1g8t2">Wake</span>
      </div>
      <hr class="quarter-break">
      <div class="one-box-round1">
        <span class="seed">1</span>
        <span draggable="true" class="team-box" id="q3r1g1t1">Carolina</span>
        <hr class="team-box-hr">
        <span class="seed">16</span>
        <span draggable="true" class="team-box" id="q3r1g1t2">ich</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">8</span>
        <span draggable="true" class="team-box" id="q3r1g2t1">ent</span>
        <hr class="team-box-hr">
        <span class="seed">9</span>
        <span draggable="true" class="team-box" id="q3r1g2t2">Y</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">12</span>
        <span draggable="true" class="team-box" id="q3r1g3t1">CL</span>
        <hr class="team-box-hr">
        <span class="seed">5</span>
        <span draggable="true" class="team-box" id="q3r1g3t2">C</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">13</span>
        <span draggable="true" class="team-box" id="q3r1g4t1">tan</span>
        <hr class="team-box-hr">
        <span class="seed">4</span>
        <span draggable="true" class="team-box" id="q3r1g4t2">riz</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">3</span>
        <span draggable="true" class="team-box" id="q3r1g5t1">nd</span>
        <hr class="team-box-hr">
        <span class="seed">14</span>
        <span draggable="true" class="team-box" id="q3r1g5t2">ichigan</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">6</span>
        <span draggable="true" class="team-box" id="q3r1g6t1">hio</span>
        <hr class="team-box-hr">
        <span class="seed">11</span>
        <span draggable="true" class="team-box" id="q3r1g6t2">tah St.</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">10</span>
        <span draggable="true" class="team-box" id="q3r1g7t1">ol</span>
        <hr class="team-box-hr">
        <span class="seed">7</span>
        <span draggable="true" class="team-box" id="q3r1g7t2">ex</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">15</span>
        <span draggable="true" class="team-box" id="q3r1g8t1">ord</span>
        <hr class="team-box-hr">
        <span class="seed">2</span>
        <span draggable="true" class="team-box" id="q3r1g8t2">ake</span>
      </div>
      <hr class="quarter-break">
      <div class="one-box-round1">
        <span class="seed">1</span>
        <span draggable="true" class="team-box" id="q4r1g1t1">N Carolina</span>
        <hr class="team-box-hr">
        <span class="seed">16</span>
        <span draggable="true" class="team-box" id="q4r1g1t2">Rich</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">8</span>
        <span draggable="true" class="team-box" id="q4r1g2t1">Kent</span>
        <hr class="team-box-hr">
        <span class="seed">9</span>
        <span draggable="true" class="team-box" id="q4r1g2t2">BY</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">12</span>
        <span draggable="true" class="team-box" id="q4r1g3t1">UCL</span>
        <hr class="team-box-hr">
        <span class="seed">5</span>
        <span draggable="true" class="team-box" id="q4r1g3t2">SC</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">13</span>
        <span draggable="true" class="team-box" id="q4r1g4t1">Stan</span>
        <hr class="team-box-hr">
        <span class="seed">4</span>
        <span draggable="true" class="team-box" id="q4r1g4t2">Ariz</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">3</span>
        <span draggable="true" class="team-box" id="q4r1g5t1">Ind</span>
        <hr class="team-box-hr">
        <span class="seed">14</span>
        <span draggable="true" class="team-box" id="q4r1g5t2">Michigan</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">6</span>
        <span draggable="true" class="team-box" id="q4r1g6t1">Ohio</span>
        <hr class="team-box-hr">
        <span class="seed">11</span>
        <span draggable="true" class="team-box" id="q4r1g6t2">Utah St.</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">10</span>
        <span draggable="true" class="team-box" id="q4r1g7t1">Col</span>
        <hr class="team-box-hr">
        <span class="seed">7</span>
        <span draggable="true" class="team-box" id="q4r1g7t2">Tex</span>
      </div>
      <div class="one-box-round1">
        <span class="seed">15</span>
        <span draggable="true" class="team-box" id="q4r1g8t1">Ford</span>
        <hr class="team-box-hr">
        <span class="seed">2</span>
        <span draggable="true" class="team-box" id="q4r1g8t2">Wake</span>
      </div>
    </div>
    <!--ROUND 2 GAMES-->
    <div class="one-column">
      <div class="one-box-round2-top">
        <span draggable="true" class="team-box" id="q1r2g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q1r2g1t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q1r2g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q1r2g2t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q1r2g3t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q1r2g3t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q1r2g4t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q1r2g4t2"></span>
      </div>

      <div class="one-box-round2-new-quarter">
        <span draggable="true" class="team-box" id="q2r2g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q2r2g1t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q2r2g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q2r2g2t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q2r2g3t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q2r2g3t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q2r2g4t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q2r2g4t2"></span>
      </div>

      <div class="one-box-round2-new-quarter">
        <span draggable="true" class="team-box" id="q3r2g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q3r2g1t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q3r2g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q3r2g2t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q3r2g3t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q3r2g3t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q3r2g4t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q3r2g4t2"></span>
      </div>

      <div class="one-box-round2-new-quarter">
        <span draggable="true" class="team-box" id="q4r2g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q4r2g1t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q4r2g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q4r2g2t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q4r2g3t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q4r2g3t2"></span>
      </div>
      <div class="one-box-round2">
        <span draggable="true" class="team-box" id="q4r2g4t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q4r2g4t2"></span>
      </div>

    </div>
    <!--ROUND 3 GAMES-->
    <div class="one-column">
      <div class="one-box-round3-top">
        <span draggable="true" class="team-box" id="q1r3g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q1r3g1t2"></span>
      </div>
      <div class="one-box-round3">
        <span draggable="true" class="team-box" id="q1r3g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q1r3g2t2"></span>
      </div>

      <div class="one-box-round3-new-quarter">
        <span draggable="true" class="team-box" id="q2r3g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q2r3g1t2"></span>
      </div>
      <div class="one-box-round3">
        <span draggable="true" class="team-box" id="q2r3g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q2r3g2t2"></span>
      </div>

      <div class="one-box-round3-new-quarter">
        <span draggable="true" class="team-box" id="q3r3g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q3r3g1t2"></span>
      </div>
      <div class="one-box-round3">
        <span draggable="true" class="team-box" id="q3r3g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q3r3g2t2"></span>
      </div>

      <div class="one-box-round3-new-quarter">
        <span draggable="true" class="team-box" id="q4r3g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q4r3g1t2"></span>
      </div>
      <div class="one-box-round3">
        <span draggable="true" class="team-box" id="q4r3g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q4r3g2t2"></span>
      </div>

    </div>
    <!--ROUND 4 GAMES-->
    <div class="one-column">
      <div class="one-box-round4-top">
        <span draggable="true" class="team-box" id="q1r4g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q1r4g1t2"></span>
      </div>
      <div class="one-box-round4">
        <span draggable="true" class="team-box" id="q2r4g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q2r4g1t2"></span>
      </div>
      <div class="one-box-round4">
        <span draggable="true" class="team-box" id="q3r4g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q3r4g1t2"></span>
      </div>
      <div class="one-box-round4">
        <span draggable="true" class="team-box" id="q4r4g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="q4r4g1t2"></span>
      </div>
    </div>

    <!--ROUND 5 GAMES-->
    <div class="one-column">
      <div class="one-box-round5-top">
        <span draggable="true" class="team-box" id="ff1r1g1t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="ff1r1g1t2"></span>
      </div>
      <div class="one-box-round5">
        <span draggable="true" class="team-box" id="ff1r1g2t1"></span>
        <hr class="team-box-hr">
        <span draggable="true" class="team-box" id="ff1r1g2t2"></span>
      </div>
    </div>

    <!--ROUND 6 GAMES-->
    <div class="one-column">
      <span class="finals-text-top">Champion</span>
      <div class="one-box-round6-top">
        <span draggable="true" class="team-box" id="ff1r2g1t1"></span>
      </div>
      <hr class="team-box-hr">
      <div class="one-box-round6">
        <span draggable="true" class="team-box" id="ff1r2g1t2"></span>
      </div>
      Finalist
    </div>

  </div>
  <!--end main-->

</section>

<br clear=all>
<hr class="bottom-break">
<!--JBB need to pretty up classes for down here-->
<!--<form id="post-picks" name="post-picks" action="process_submission.php" method="post">-->
<form id="post-picks" name="post-picks" action="processor.cgi" method="post">
  <input type=text size=20 name="username" placeholder="username"><br>
  <input type=password size=20 name="password" placeholder="choose a password"><br>
  <input type=hidden size=20 name="token" value="jay"><br>
  <input type=hidden id="picks" name="picks">
</form>
<button type="button" id="submit-button" class="submit-button" onclick="handleSubmit();">Submit Picks</button><br>

<script>
var dragSrcEl = null;
var dropZoneId = null;
var dragSrcId = null;

function handleDragStart(event) {
  //this.style.opacity = '0.4';
  this.classList.add('selected');

  dragSrcEl = this;

  event.dataTransfer.effectAllowed = 'copy';
  event.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragOver(event) {
  if (event.preventDefault) {
    event.preventDefault();
  }
  event.dataTransfer.dropEffect = 'copy';

  return false;
}

function handleDragEnter(event) {
  //this.addClassName('over');
  if (!(this === dragSrcEl)) {
    //See that it is an eligible drop point. i.e., deeper in
    //tournament and on same path (same quarter, etc.).
    //["q1r1g1t1", "q", "1", "1", "1", "1"]
    dragSrcId = 
      RegExp(/(\w+)(\d+)r(\d+)g(\d+)t(\d+)/).exec(dragSrcEl.id);
    dropZoneId = RegExp(/(\w+)(\d+)r(\d+)g(\d+)t(\d+)/).exec(this.id);
    //if q1 or q2 then ff1r1 or ff1r2. if q3/q4 then ff2f1 or ff1r2
    var quarterValid = false;
    var roundValid = false;
    var gameValid = false;
    var teamValid = false;
    var finalFourGame = Math.ceil(dragSrcId[2]/2); //1 or 2
    //valid quarter
    if 
      ((dropZoneId[2] === dragSrcId[2]) ||  
      ((dropZoneId[1] == "ff") && (dropZoneId[4] == finalFourGame)) ||
      ((dropZoneId[1] == "ff") && (dropZoneId[3] == 2)))
    {
      quarterValid = true;
    }
    //valid round
    if (quarterValid) {
      if 
      ((dropZoneId[3] > dragSrcId[3]) ||  
      ((dropZoneId[1] == "ff") && (dragSrcId[1] == "q")) ||
      ((dragSrcId[1] == "ff") && (dropZoneId[1] == "ff" ) &&
      (dropZoneId[3] == 2)))
      {
        roundValid = true;
      }
    }
    // valid game
    if (quarterValid && roundValid) {
      var denominator = Math.pow(2, dropZoneId[3] - dragSrcId[3]);
      var gameCalc = Math.ceil(dragSrcId[4] / denominator);
      if 
      ((gameCalc == dropZoneId[4]) || 
      ((parseInt(dropZoneId[4]) == finalFourGame) && (dropZoneId[1] == "ff")) ||
      ((dropZoneId[1] == "ff" ) && (dropZoneId[3] == 2)))
      
      {
        gameValid = true;
      }
    }
    // valid team
    if (quarterValid && roundValid && gameValid) {
      //find game in round before. Odd means team 1.
      var denominator = Math.pow(2, dropZoneId[3] - dragSrcId[3] - 1);
      var priorGameCalc = Math.ceil(dragSrcId[4] / denominator);
      priorGameCalc = priorGameCalc % 2;
      qtrSrcOddEven = dragSrcId[2] % 2;
      switch(dropZoneId[1])
      {
      case 'q':
        if 
        (((priorGameCalc == 0 ) && (dropZoneId[5] == 2)) || 
        ((priorGameCalc == 1 ) &&  (dropZoneId[5] == 1)))
        {
          teamValid = true;
        }
        break;
      case 'ff':
        if (dropZoneId[3] == 2) {
          // compare champ and finalist to be sure different
          var checkEl;
          var checkId;
          dropZoneId[5] == 1 ? checkId = 'ff1r2g1t2' : checkId = 'ff1r2g1t1';
          checkEl = document.getElementById(checkId);
          if (checkEl) {
            if (checkEl.innerHTML != dragSrcEl.innerHTML) {
              teamValid = true;
            }
          }
        } else if 
          (((dropZoneId[5] == 1) && (qtrSrcOddEven == 1 )) ||
          ((dropZoneId[5] == 2) && (qtrSrcOddEven == 0 )))
        {
          teamValid = true;
        }
        break;
      }
    }

    if (quarterValid && roundValid && gameValid && teamValid) {
      if (!this.classList.contains('over')) {
        this.classList.add('over');
      }
    }
  }
}

function handleDragLeave(event) {
  //this.removeClassName('over');
  if (this.classList.contains('over')) {
    this.classList.remove('over');
  }
}

function handleDrop(event) {
  if (event.stopPropagation) {
    event.stopPropagation();
  }

  if ((dragSrcEl != this) && (this.classList.contains('over'))) {
    //dragSrcEl.innerHTML = this.innerHTML; //swap
    //dragSrcEl.style.opacity = '1.0';
    dragSrcEl.classList.remove('selected');
    forwardFill(dropZoneId, this.innerHTML, dragSrcEl.innerHTML);
    backFill(dragSrcId, dropZoneId);
    this.innerHTML = event.dataTransfer.getData('text/html');
    // JBB if champ see if finalist exists and is from same quarter
    // if so, need to wipe it. Also need to set half value for this team
    if ((dropZoneId[1] == 'ff') && (dropZoneId[3] == 2)) {
      if (dropZoneId[5] == 2) {
        finalistHalf = calculateHalf(dragSrcId);
        if (championHalf === finalistHalf) {
          document.getElementById('ff1r2g1t1').innerHTML = "";
        }
      } else {
        championHalf = calculateHalf(dragSrcId);
        if (championHalf === finalistHalf) {
          document.getElementById('ff1r2g1t2').innerHTML = "";
        }
      }
    }

    but = document.getElementById('autoFill')
    but.disabled = true;
    //but.hidden = true;
  }

  return false;
}

function handleDragEnd(event) {
  [].forEach.call(teams, function (team) {
    team.classList.remove('over');
  });
  dragSrcEl.classList.remove('selected');
}

function calculateHalf(srcId) {
  if 
  (
  ((srcId[1] == 'ff') && (srcId[4] == 2)) ||
  ((srcId[1] == 'q') && (parseInt(srcId[2]) > 2))
  )
  {
    return 2;
  } else {
    return 1;
  }
}


// Replace all forward instances of old content with new
function forwardFill(target, oldContent, newContent) {
  if ((target[1] == "ff") && (target[3] == 2)) { return; }
  var round = parseInt(target[3]);
  [].forEach.call(teams, function(team) {
    if ((team.innerHTML == oldContent) && (oldContent != "")) {
      var oldContentArr = RegExp(/(\w+)(\d+)r(\d+)g(\d+)t(\d+)/).exec(team.id);
      if (((parseInt(oldContentArr[3]) > round) && (target[1] != "ff")) || 
        ((oldContentArr[1] == "ff") && (dragSrcId[1] != "ff"))) 
      {
        team.innerHTML = newContent; 
      }
    }
  });
}

// recursively populate earlier rounds with selection
function backFill(src, target) {
  if 
    (((parseInt(target[3], 10) - parseInt(src[3], 10)) > 1) || 
    ((target[1] == "ff") && (src[1] == "q"))) 
  {
    //do calculation to get correct ID and set value
    var nextId = "q" + src[2] + "r" + (parseInt(src[3], 10) + 1); //qtr & rd
    nextId += "g" + (Math.round(src[4] / 2)) + "t"; 
    var priorGameCalc = src[4] % 2;
    (priorGameCalc == 1 ? nextId += "1" : nextId += "2");
    var nextEl = document.getElementById(nextId);
    if (nextEl != null) {
      nextEl.innerHTML = dragSrcEl.innerHTML; 
      var nextSrcArr = 
        RegExp(/(\w+)(\d+)r(\d+)g(\d+)t(\d+)/).exec(nextEl.id);
      backFill(nextSrcArr, target);
    } else if ((parseInt(target[3], 10) == 2) && (src[1] != "ff")) { 
      // using fact that a ff target from q src will eventually go null
      //3 ff teams and two rounds. If ff1r2 (champ) then populate ff1r1
      switch(src[2])
      {
      case '1':
        nextEl = document.getElementById('ff1r1g1t1');
        nextEl.innerHTML = dragSrcEl.innerHTML; 
        break;
      case '2':
        nextEl = document.getElementById('ff1r1g1t2');
        nextEl.innerHTML = dragSrcEl.innerHTML; 
        break;
      case '3':
        nextEl = document.getElementById('ff1r1g2t1');
        nextEl.innerHTML = dragSrcEl.innerHTML; 
        break;
      case '4':
        nextEl = document.getElementById('ff1r1g2t2');
        nextEl.innerHTML = dragSrcEl.innerHTML; 
        break;
      }
    }
  }
}

function handleClear() {
  [].forEach.call(teams, function(team) {
    var oldContentArr = 
      RegExp(/(\w+)(\d+)r(\d+)g(\d+)t(\d+)/).exec(team.id);
    if ((parseInt(oldContentArr[3]) > 1) || (oldContentArr[1] == 'ff')) {
        team.innerHTML = ""; 
    }
  });
  document.getElementById('autoFill').disabled = false;
  finalistHalf = null;
  championHalf = null;
}

function handleAutofill(teams) {
  teams = typeof(teams) == 'undefined' ? roundOneTeams : teams;
  var nextTeams = {};
  var nextRd;
  var nextGm;
  var idArr = [];
  var nextId;
  var validNextTeam = false;
  var recurse = false;
  var nextEl;
  for (var key in teams) {
    switch(key)
    {
    case 'q1r1g1t1':
      el = document.getElementById('ff1r1g1t1');
      if (el) { el.innerHTML = teams[key]; }
      el = document.getElementById('ff1r2g1t1');
      if (el) { el.innerHTML = teams[key]; }
      break;
    case 'q2r1g1t1':
      el = document.getElementById('ff1r1g1t2');
      if (el) { el.innerHTML = teams[key]; }
      break;
    case 'q3r1g1t1':
      el = document.getElementById('ff1r1g2t1');
      if (el) { el.innerHTML = teams[key]; }
      el = document.getElementById('ff1r2g1t2');
      if (el) { el.innerHTML = teams[key]; }
      break;
    case 'q4r1g1t1':
      el = document.getElementById('ff1r1g2t2');
      if (el) { el.innerHTML = teams[key]; }
      break;
    }

    var idArr = RegExp(/(\w+)(\d+)r(\d+)g(\d+)t(\d+)/).exec(key);
    var gm = parseInt(idArr[4]);
    var tm = parseInt(idArr[5]);
    nextRd = parseInt(idArr[3]) + 1;
    nextGm = Math.ceil(gm / 2);
    nextId = "q" + idArr[2] + "r" + nextRd + "g" + nextGm;
    validNextTeam = false;
    // % 4 for round 1, % 2 subsequently
    if (parseInt(idArr[3]) == 1) {
      var mod = gm % 4;
      if (((mod == 1) || (mod == 2)) && (tm == 1)) {
        validNextTeam = true;
        nextId += mod == 1 ? "t1" : "t2";
      } else if (((mod == 0) || (mod == 3)) && (tm == 2)) {
        validNextTeam = true;
        nextId += mod == 0 ? "t2" : "t1";
      }
    } else {
      if (((gm % 2 ) == 1) && (tm == 1)) {
        nextId += "t1";
        validNextTeam = true;
      } else if (((gm % 2 ) == 0) && (tm == 2)) {
        nextId += "t2";
        validNextTeam = true;
      }
    }    
    if (validNextTeam) { 
      nextEl = document.getElementById(nextId);
      if (nextEl) {
        nextEl.innerHTML = teams[key];
        nextTeams[nextId] = teams[key];
        recurse = true;
      }
    }
  }
  if (recurse) {
    recurse = false;
    handleAutofill(nextTeams);
  }
  document.getElementById('autoFill').disabled = true;
}

var roundOneTeams = [];  // id => name
var teams = document.querySelectorAll('.team-box');
[].forEach.call(teams, function(team) {
  team.addEventListener('dragstart', handleDragStart, false);
  team.addEventListener('dragenter', handleDragEnter, false);
  team.addEventListener('dragover', handleDragOver, false);
  team.addEventListener('dragleave', handleDragLeave, false);
  team.addEventListener('drop', handleDrop, false);
  team.addEventListener('dragend', handleDragEnd, false);
  var teamArr = RegExp(/(\w+)(\d+)r(\d+)g(\d+)t(\d+)/).exec(team.id);
  if 
    ((parseInt(teamArr[3]) == 1) && 
    (teamArr[1] == 'q'))
  {
    roundOneTeams[team.id] = team.innerHTML;
  } 
});

function handleSubmit() {
  var theForm = document.getElementById('post-picks');
  //theForm.password.value = "test password";
  if (theForm.username.value == "") {
    alert("Please provide a user name");
  } else {
  var picks = "";
    
    [].forEach.call(teams, function(team) {
      //{team.id: team.innerHTML;}
      //want the above JSON in the picks form var
      //theForm.picks.value = eval(json_str);;
      picks += team.id + "," + team.innerHTML + ",";
    });
    theForm.picks.value = picks;
    theForm.submit();
  }    
}

</script>

</body>
</html>

</body>
</html>
