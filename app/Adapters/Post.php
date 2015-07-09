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
class Post extends Base implements AdapterContract
{
    const ID         = 'id';
    const TITLE      = 'title';
    const BODY       = 'body';
    const USER       = 'user';
    const COMMENT    = 'comment';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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
            $this->keyExists(self::ID)         => PostModel::ID,
            $this->keyExists(self::TITLE)      => PostModel::TITLE,
            $this->keyExists(self::BODY)       => PostModel::BODY,
            $this->keyExists(self::USER)       => PostModel::USER_ID,
            $this->keyExists(self::CREATED_AT) => PostModel::CREATED_AT,
            $this->keyExists(self::UPDATED_AT) => PostModel::UPDATED_AT,
        ]);
    }

    /**
     * @author Rohit Arora
     *
     * @param $fields
     * @param $list
     *
     * @return array
     */
    public function reFilter($fields, $list)
    {
        if (array_has($fields, self::USER)) {
            unset($fields[PostModel::USER_ID]);
            $fields[self::USER] = self::USER;
        }

        return parent::reFilter($fields, $list);
    }
}