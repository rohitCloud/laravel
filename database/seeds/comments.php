<?php

use Illuminate\Database\Seeder;

/**
 * @author Rohit Arora
 *
 * Class comments
 */
class Comments extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Laracasts\TestDummy\Factory::times(50)
                                   ->create(\App\Models\Comment::class);
    }
}
