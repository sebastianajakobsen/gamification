<?php

namespace App\Achievements;

use Gstt\Achievements\Achievement;

class UserMadeACharacter extends Achievement
{
    /*
     * The achievement name
     */
    public $name = "The Journey Begins";

    /*
     * A small description for the achievement
     */
    public $description = "Create your first character";

    public $points = 50;

    public $icon = 'a1.jpg';

}