<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Product')->insert([
        	[
        		'id' => '1credits',
        		'credits' => 25
        	],
        	[
        		'id' => '2credits',
        		'credits' => 57
        	],
        	[
        		'id' => '5credits',
        		'credits' => 166
        	],
        	[
        		'id' => '10credits',
        		'credits' => 400
        	],
        	[
        		'id' => '20credits',
        		'credits' => 1000
        	],
        	[
        		'id' => '50credits',
        		'credits' => 3333
        	],
        	[
        		'id' => '100credits',
        		'credits' => 10000
        	]
        ]);
    }
}
