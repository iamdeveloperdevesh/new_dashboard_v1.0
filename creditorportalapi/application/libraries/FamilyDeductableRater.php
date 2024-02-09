<?php
defined('BASEPATH') or exit('No direct script access allowed');

class FamilyDeductableRater extends Rater
{
    protected $adult_count;

    protected $child_count;

    protected $deductable;

    public function getResults()
    {
        $this->adult_count = $this->data['adults_to_calculate'];
        $this->child_count = $this->data['children_to_calculate'];
        $this->deductable = $this->data['deductable'];

        $result = $this->apimodel->getPolicyFamilyDeductable(array_merge($this->getDefaultArguments(), ['adult_count' => $this->adult_count,  "child_count" => $this->child_count, 'deductable' => $this->deductable]));
        
        return $result;
    }
}