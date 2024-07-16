<?php

use raylib\Vector2;
use raylib\Texture;
use raylib\Rectangle;

class Ship {
    function __construct(Vector2 $pos, float $velocity, float $acceleration, float $radius, float $angle, Texture $texture) {
        $this->pos = $pos;
        $this->velocity = $velocity;
        $this->acceleration = $acceleration;

        // TODO: remove radius.
        $this->radius = $radius;
        $this->angle = $angle;

        $this->texture = $texture;

        $this->bullets = array();

        $this->score = 0;
        $this->life = 2;
        $this->game_lost = false;
    }

    public $pos;
    public $velocity;
    public $acceleration;

    public $radius;
    public $angle;

    public $texture;

    public $bullets;

    public $score;
    public $life;
    public $game_lost;
};

function move_ship(Ship &$ship) {
    $ship->velocity += $ship->acceleration * GetFrameTime();
    if ($ship->acceleration > 20) $ship->acceleration = 20.0;
    if ($ship->velocity > 300) $ship->velocity = 300;

    $ship->pos->x += $ship->velocity * cos(d2r($ship->angle)) * GetFrameTime();
    $ship->pos->y += $ship->velocity * sin(d2r($ship->angle)) * GetFrameTime();
}

function ship_out_of_bounds(&$ship) {
    global $width, $height;
    if ($ship->pos->x - $ship->radius < 0) {
        $ship->pos->x = $width - $ship->radius;
    }
    elseif ($ship->pos->x + $ship->radius > $width) {
        $ship->pos->x = $ship->radius;
    }
    if ($ship->pos->y - $ship->radius < 0) {
        $ship->pos->y = $height - $ship->radius;
    }
    elseif ($ship->pos->y + $ship->radius  > $height) {
        $ship->pos->y = $ship->radius;
    }
}


function ship_collision(&$asteroids, &$ship) {
    for ($i = 0; $i < count($asteroids); $i++) {
        $asteroids_hitbox = new Rectangle(
            $asteroids[$i]->pos->x-$asteroids[$i]->radius, 
            $asteroids[$i]->pos->y-$asteroids[$i]->radius, 
            $asteroids[$i]->radius+$asteroids[$i]->radius, 
            $asteroids[$i]->radius+$asteroids[$i]->radius);

        $ship_hitbox = new Rectangle(
            $ship->pos->x,
            $ship->pos->y,
            $ship->radius,
            $ship->radius);

        if (CheckCollisionRecs($asteroids_hitbox, $ship_hitbox)) {
            $ship->score++;
            $ship->life--;
            if ($ship->life <= 0) $ship->game_lost = true;
            array_splice($asteroids, $i, 1);
        }
    }
}

function create_ship(&$texture) {
    global $width, $height;

    return new Ship(
        new Vector2($width/2, $height/2),
        0,
        0,

        $texture->height,
        270.0,

        $texture
    );
}
?>
