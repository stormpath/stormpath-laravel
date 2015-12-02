<?php

namespace Stormpath\Laravel\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as Validator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LoginController extends Controller
{

    /**
     * @var Request
     */
    private $request;
    /**
     * @var Validator
     */
    private $validator;


    /**
     * LoginController constructor.
     * @param Request $request
     * @param Validator $validator
     */
    public function __construct(Request $request, Validator $validator)
    {
        $this->request = $request;
        $this->validator = $validator;
    }

    public function getLogin()
    {
        $status = $this->request->get('status');

        return view( config('stormpath.web.login.view'), compact('status') );
    }

    public function postLogin(Request $request)
    {

        $validator = $this->validator->make(
            $request->all(),
            [
                'login' => 'required',
                'password' => 'required',
            ],
            [
                'login.required' => 'Login is required.',
                'password.required' => 'Password is required.'
            ]
        );

        if($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


    }
}