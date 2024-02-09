<?php
defined('BASEPATH') or exit('No direct script access allowed');

class FlatRater extends Rater
{
    public function getResults()
    {
        $result = $this->apimodel->getPolicyPremiumFlat($this->getDefaultArguments());
        return $result;
    }
}