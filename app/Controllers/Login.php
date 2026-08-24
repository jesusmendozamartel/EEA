<?php

namespace App\Controllers;

use App\Models\LoginModel;

class Login extends BaseController
{
    public function index()
    {
        return view('login/index');
    }

    public function main()
    {
        return view('main/principal_view');
    }

    public function valida()
    {
        $model = new \App\Models\LoginModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $result = $model->valida($username, $password);

        if ($result) {

            session()->set([
                'username'  => $username,
                'logged_in' => true
            ]);
        }

        return $this->response->setJSON($result);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}