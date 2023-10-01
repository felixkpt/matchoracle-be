<?php

namespace App\Http\Controllers\Admin\Posts;

use App\Http\Controllers\Controller;
use App\Repositories\Post\PostRepositoryInterface;
use App\Services\Validations\Post\PostValidationInterface;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    function __construct(
        private PostRepositoryInterface $postRepositoryInterface,
        private PostValidationInterface $postValidationInterface
    ) {
    }

    public function index()
    {
        return $this->postRepositoryInterface->index();
    }

    public function store(Request $request)
    {

        $data = $this->postValidationInterface->store($request);

        return $this->postRepositoryInterface->store($request, $data);
    }
}
