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
    const DEFAULT_OFFSET    = 0;
    const DEFAULT_LIMIT     = 10;
    const DEFAULT_SORT_BY   = PostModel::ID;
    const DEFAULT_SORT_TYPE = Base::SORT_ASC;

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
                    ->get();
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $postID
     * @param array $parameters
     *
     * @return $this
     */
    public function getByID($postID, $parameters = [ALL_FIELDS])
    {
        return $this->setRequestParameters($parameters)
                    ->find($postID);
    }

    /**
     * @author Rohit Arora
     *
     * @param $by
     *
     * @return bool
     */
    public static function isValidOrderBy($by)
    {
        return PostModel::isValidOrderBy($by);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return mixed
     */
    public function store($parameters)
    {
        return $this->setPostParameters($parameters)
                    ->save();
    }
}