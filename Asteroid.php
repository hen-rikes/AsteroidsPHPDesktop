<?php

use raylib\Vector2;
use raylib\Color;
use raylib\Rectangle;

class Asteroid {
    function __construct(Vector2 $pos, Vector2 $velocity, float $acceleration, int $lines_num, float $radius) {
        $this->pos = $pos;
        $this->velocity = $velocity;
        $this->acceleration = $acceleration;
        $this->lines_num = $lines_num;
        $this->radius = $radius;
    }

    public $pos;
    public $velocity;
    public $acceleration;
    public $lines_num;
    public $radius;
};

function create_asteroids(&$arry, $num_of_asteroids, &$ship) {
    global $width, $height, $asteroids_max_speed;

    for ($i = 0; $i < $num_of_asteroids; $i++) {
        $radius = rand(20, 40);
        $asteroid_pos = new Vector2(rand($radius, $width - $radius), rand($radius, $height - $radius));

        $asteroids_hitbox = new Rectangle(
            $asteroid_pos->x-$radius, 
            $asteroid_pos->y-$radius, 
            $radius+$radius, 
            $radius+$radius);

        $ship_hitbox = new Rectangle(
            $ship->pos->x,
            $ship->pos->y,
            $ship->texture->width,
            $ship->texture->height);
        while (CheckCollisionRecs($asteroids_hitbox, $ship_hitbox)) {
            $asteroid_pos = new Vector2(rand($radius, $width - $radius), rand($radius, $height - $radius));
            $asteroids_hitbox = new Rectangle(
                $asteroid_pos->x-$radius, 
                $asteroid_pos->y-$radius, 
                $radius+$radius, 
                $radius+$radius);
        }

        array_push($arry, new Asteroid(
            $asteroid_pos,
            new Vector2(rand(-$asteroids_max_speed, $asteroids_max_speed), rand(-$asteroids_max_speed, $asteroids_max_speed)),
            0,
            7,
            $radius)
        );
    }
}

function draw_asteroids(&$arry) {
    foreach ($arry as &$a) {
        DrawPolyLines($a->pos, $a->lines_num, $a->radius, 0, Color::WHITE());
    }
}

function destroy_asteroids(&$bullets, &$asteroids, &$score) {
    for ($i = 0; $i < count($bullets); $i++) {
        for ($j = 0; $j < count($asteroids); $j++) {
            $asteroids_hitbox = new Rectangle(
                $asteroids[$j]->pos->x-$asteroids[$j]->radius, 
                $asteroids[$j]->pos->y-$asteroids[$j]->radius, 
                $asteroids[$j]->radius+$asteroids[$j]->radius, 
                $asteroids[$j]->radius+$asteroids[$j]->radius);

            if (CheckCollisionCircleRec($bullets[$i]->pos, $bullets[$i]->radius, $asteroids_hitbox)) {
                array_splice($asteroids, $j, 1);
                array_splice($bullets, $i, 1);
                // TODO: properly handle the score.
                $score++;
                break;
                // TODO: add four(maybe a random number instead of 4) more asteroids. 
            }
        }
    }
}

// TODO: Set the magnitude of the velocity.
function move_asteroids(&$arry) {
    global $width, $height;

    foreach ($arry as &$a) {
        $a->pos->x += $a->velocity->x * GetFrameTime();
        $a->pos->y += $a->velocity->y * GetFrameTime();

        if ($a->pos->x - $a->radius < 0) {
            $a->pos->x = $width - $a->radius;
        }
        elseif ($a->pos->x + $a->radius > $width) {
            $a->pos->x = $a->radius;
        }
        if ($a->pos->y - $a->radius < 0) {
            $a->pos->y = $height - $a->radius;
        }
        elseif ($a->pos->y + $a->radius > $height) {
            $a->pos->y = $a->radius;
        }
    }
}

?>
