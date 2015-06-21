<?php

function getInfoAreas() {
    return "<div class='progress'>
      <div id='progress-bar' class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='45' aria-valuemin='0' aria-valuemax='100' style='width: 100%'>
        0%
      </div>
    </div><div id='progressbar-info'></div>";
}
function updateInfo($percent, $infoText, $debug = true) {
    if ($debug) {
        Debug::message($infoText, Debug::WARNING);
    }
    $percentageText = ($percent == 1)? '100': sprintf('%2.0f', $percent * 100);
    echo '<script>';
    if ($percent >= 0) {
        echo 'document.getElementById("progress-bar").style="width:' . ($percent * 100) . '%;";', "\n",
             'document.getElementById("progress-bar").innerHTML ="' . $percentageText . '%";', "\n";
    }
    if ($percent == 1) {
        echo 'document.getElementById("progress-bar").className = "progress-bar progress-bar-striped";';
    }
    echo 'document.getElementById("progressbar-info").innerHTML="' . addslashes($infoText) . '";</script>';
    // This is for the buffer achieve the minimum size in order to flush data
//    echo str_repeat(' ', 1024 * 64);
    // Send output to browser immediately
    flush();
}
