<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Post;

use App\Adapters\Post as PostAdapter;
use App\Contracts\Repositories\Post as PostContract;
use App\Models\Post as PostModel;
use App\Repositories\Base;

/**
 * @author Rohit Arora
 */
class Post extends Base implements PostContract
{
    const OFFSET    = 0;
    const LIMIT     = 10;
    const SORT_BY   = PostModel::ID;
    const SORT_TYPE = Base::SORT_ASC;

    /**
     * @param PostModel   $Model
     *
     * @param PostAdapter $Adapter
     */
    public function __construct(PostModel $Model, PostAdapter $Adapter)
    {
        parent::__construct($Model, $Adapter);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function fetch($parameters)
    {
        return $this->setRequestParameters($parameters)
                    ->setDataFromModel()
                    ->process();
    }

    /**
     * @author Rohit Arora
     *
     * @param $postID
     *
     * @return Post
     */
    public function getByID($postID)
    {
        return $this->setRequestParameters(['embed' => true])
                    ->find($postID);
    }
}