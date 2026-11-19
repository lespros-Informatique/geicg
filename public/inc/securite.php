<?php

if (!(Session::has('client'))) {
    redirect();
}
