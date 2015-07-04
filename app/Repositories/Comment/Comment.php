<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Comment;

use App\Adapters\Comment as CommentAdapter;
use App\Contracts\Repositories\Comment as CommentContract;
use App\Models\Comment as CommentModel;
use App\Repositories\Repository;

/**
 * @author Rohit Arora
 */
class Comment extends Repository implements CommentContract
{
    /**
     * @param CommentModel   $Model
     *
     * @param CommentAdapter $Adapter
     *
     * @internal param CommentModel $Comment
     */
    public function __construct(CommentModel $Model, CommentAdapter $Adapter)
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
    public function get($parameters)
    {
        $parameters = $this->Adapter->filter(isset($parameters['fields']) ? explode(',', $parameters['fields']) : ['*']);

        if (!$parameters) {
            return [];
        }

        return $this->Adapter->reFilter($parameters, $this->fetch($parameters)
                                                          ->toArray());
    }
}