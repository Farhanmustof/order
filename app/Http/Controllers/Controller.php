<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /** ID pengguna yang sedang login, diteruskan ke use case sebagai pelaku. */
    protected function actorId(): int
    {
        return (int) auth()->id();
    }
}
