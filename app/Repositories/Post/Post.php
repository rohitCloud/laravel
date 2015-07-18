<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Post;

use App\Adapters\Post as PostAdapter;
use App\Contracts\Repositories\Post as PostContract;
use App\Contracts\Repositories\User;
use App\Exceptions\InvalidData;
use App\Models\Post as PostModel;
use App\Models\User\User as UserModel;
use App\Repositories\Base;
use Illuminate\Database\Eloquent\Builder;

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
     * @var User
     */
    private $User;

    /**
     * @param PostModel   $Model
     *
     * @param PostAdapter $Adapter
     * @param User        $User
     */
    public function __construct(PostModel $Model, PostAdapter $Adapter, User $User)
    {
        parent::__construct($Model, $Adapter);
        $this->User = $User;
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
     * @return array
     * @throws InvalidData
     */
    public function store($parameters)
    {
        $this->setPostParameters($parameters);
        if (!$this->User->exists($this->getData()[PostModel::USER_ID])) {
            throw new InvalidData("User does not exists!");
        }

        return $this->save();
    }

    /**
     * @author Rohit Arora
     *
     * @param array $parameters
     * @param int   $userID
     *
     * @return array
     */
    public function getPostsByUser($parameters, $userID)
    {
        return $this->setRequestParameters($parameters)
                    ->getPostRelatedToUser($userID)
                    ->get();
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $userID
     * @param int   $postID
     * @param array $parameters
     *
     * @return array
     */
    public function getByUserAndID($userID, $postID, $parameters = [ALL_FIELDS])
    {
        return $this->setRequestParameters($parameters)
                    ->getPostRelatedToUser($userID)
                    ->find($postID);
    }

    /**
     * @author Rohit Arora
     *
     * @param int $userID
     *
     * @return Post
     */
    public function getPostRelatedToUser($userID)
    {
        return $this->setQueryBuilder($this->getQueryBuilder()
                                           ->whereHas('user', function ($query) use ($userID) {
                                               /* @var Builder $query */
                                               $query->where(UserModel::ID, EQUAL, $userID);
                                           }));
    }
}