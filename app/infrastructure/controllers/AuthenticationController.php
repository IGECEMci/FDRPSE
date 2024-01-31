<?php

namespace App\infrastructure\controllers;

use Exception;
use App\application\authentication\AuthenticationUseCase;
use App\Http\Controllers\Controller;
use App\infrastructure\requests\authentication\LoginRequest;

class AuthenticationController extends Controller
{

    public function __construct(private readonly AuthenticationUseCase $authenticationUseCase)
    {
    }

    public function login()
    {
        $this->validate(LoginRequest::rules(), LoginRequest::messages());

        $user = $this->authenticationUseCase->signin($this->post('email'), $this->post('email'));
        if (!($user instanceof Exception)) return $this->response($this->createSession($user));
        $this->response($user);
    }

    public function revalidateToke()
    {
        $session = $_SERVER['HTTP_SESSION'] ?? "";
        $user = $this->check($session);
        if(!$user) return $this->response(['message' => 'Unathorized'], 401);
        $user = $this->authenticationUseCase->checkUserSession($user);
        if($user instanceof Exception) return $this->response($user);
        $this->response($this->createSession($user));
    }
}