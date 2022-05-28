<?php

use Gazelle\Manager\Donation;

$donation = new Donation();
$count = $donation->expireRanks();
echo "Expire $count users donor rank";
