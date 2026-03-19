<?php
echo shell_exec('composer --version 2>&1');
echo '<br>';
echo shell_exec('php --version 2>&1');
echo '<br>';
echo shell_exec('which composer 2>&1');