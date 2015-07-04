<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Post;

use App\Contracts\Repository as ContractContract;
use App\Models\Post as PostModel;
use App\Repositories\Repository;
use App\Adapters\Post as PostAdapter;

/**
 * @author Rohit Arora
 */
class Post extends Repository implements ContractContract
{
    /**
     * @param PostModel   $Model
     *
     * @param PostAdapter $Adapter
     *
     * @internal param PostModel $Post
     */
    public function __construct(PostModel $Model, PostAdapter $Adapter)
    {
        parent::__construct($Model, $Adapter);
    }
}