<?php

namespace robrogers3\Laracastle;

interface UserInterface
{
    public function recentlyVerified();

    public function resetAccountPassword();
}
