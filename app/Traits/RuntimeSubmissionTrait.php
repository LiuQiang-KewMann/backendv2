<?php namespace App\Traits;

use App\Models\Component;

trait RuntimeSubmissionTrait
{
    /*
     * mark a a particular submission and return result
     */
    public function marking()
    {
        $solution = $this->jsonGet('solution');
        $operator = $this->operator;
        $submission = $this->submission;

        // check submission with solution by operator
        switch ($operator) {
            case Component::OPERATOR_GT:
                // greater than
                $result = ($submission > $solution);
                break;

            case Component::OPERATOR_LT:
                // less than
                $result = ($submission < $solution);
                break;

            case Component::OPERATOR_NLT:
                // no less than
                $result = ($submission >= $solution);
                break;

            case Component::OPERATOR_NGT:
                // no greater than
                $result = ($submission <= $solution);
                break;

            case Component::OPERATOR_EQ:
                // equal
                $result = (strtolower($submission) == strtolower($solution));
                break;

            default:
                // default is: free
                $result = 1;
        }

        return $result;
    }
}