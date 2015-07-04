<?php
/**
 * @author Rohit Arora
 */

namespace App\Adapters;

use App\Contracts\Adapter as AdapterContract;
use App\Models\Post as PostModel;

/**
 * @author  Rohit Arora
 *
 * Class Post
 * @package App\Adapters
 */
class Post extends Adapter implements AdapterContract
{
    const ID    = 'id';
    const TITLE = 'title';
    const BODY  = 'body';
    const USER  = 'user';

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     *
     * @return array
     */
    public function filter($fields = ['*'])
    {
        $this->fields = $fields;

        return $this->clean([
            $this->keyExists(self::ID)    => PostModel::ID,
            $this->keyExists(self::TITLE) => PostModel::TITLE,
            $this->keyExists(self::BODY)  => PostModel::BODY,
            $this->keyExists(self::USER)  => PostModel::USER_ID
        ]);
    }
}