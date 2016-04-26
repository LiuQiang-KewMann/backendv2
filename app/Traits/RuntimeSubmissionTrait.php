<?php namespace App\Traits;

use App\Models\Component;

trait RuntimeSubmissionTrait
{
    public function marking()
    {
        // check submission with solution by operator
        switch ($this->operator) {
            case Component::OPERATOR_GT:
                // greater than
                $result = ($this->submission > $this->solution);
                break;

            case Component::OPERATOR_LT:
                // less than
                $result = ($this->submission < $this->solution);
                break;

            case Component::OPERATOR_NLT:
                // no less than
                $result = ($this->submission >= $this->solution);
                break;

            case Component::OPERATOR_NGT:
                // no greater than
                $result = ($this->submission <= $this->solution);
                break;

            case Component::OPERATOR_EQ:
                // equal
                $result = (strtoupper($this->submission) == strtoupper($this->solution));
                break;

            default:
                // default is: free
                $result = 1;
        }

        return $result;
    }
}