<?php
use think\facade\Route;

Route::get('tty', function () {

    $html = <<<HTML
<!doctype html>
<title>phptty</title>
<script src="/term.js"></script>
<style>
  html {
    background: #555;
  }

  h1 {
    margin-bottom: 20px;
    font: 20px/1.5 sans-serif;
  }


  .terminal {
    float: left;
    border: #000 solid 5px;
    font-family: Consolas;
    font-size: 14px;
    color: #f0f0f0;
    background: #000;
  }

  .terminal-cursor {
    color: #000;
    background: #f0f0f0;
  }

</style>
<script>
    if(window.WebSocket){
        window.addEventListener('load', function() {
            var socket = new WebSocket("ws://127.0.0.1:7778");
            socket.onopen = function() {
                var term = new Terminal({
                    cols: 130,
                    rows: 50,
                    cursorBlink: false
                });
                term.open(document.body);
                term.on('data', function(data) {
                    socket.send(data);
                });
                socket.onmessage = function(data) {
                    term.write(data.data);
                };
                socket.onclose = function() {
                    term.write("Connection closed.");
                };
            };
        }, false);
    }
    else {
        alert("Browser do not support WebSocket.");
    }
</script>
HTML;

    return $html;
});