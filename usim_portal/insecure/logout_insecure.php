<?php
// Target the vulnerable environment's session namespace
session_name('USIM_INSECURE_SESSION'); 
session_start();

// ... the rest of your exact same logout code ...