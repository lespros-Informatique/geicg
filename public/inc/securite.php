<?php

if (!(Session::has('client'))) {
    header('location: '.RACINE);
}
