<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    // default Eloquent setting
    protected $guarded = ['id'];
    protected $hidden = ['json'];
    public $timestamps = false;

    /**
     * Get given attributes from whole set
     *
     * @param array $only
     * @return array
     */
    public function getAttributesOnly($only = [])
    {
        $attributes = parent::getAttributes();

        return array_only($attributes, $only);
    }


    /**
     * Get filtered attributes from whole set
     *
     * @param array $except
     * @return array
     */
    public function getAttributesExcept($except = [])
    {
        $attributes = parent::getAttributes();

        return array_except($attributes, $except);
    }


    /**
     * Compare id with current object
     *
     * @param int $id
     * @return bool
     */
    public function compareId($id = null)
    {
        return ($id && ($this->id == $id));
    }


    /**
     * @param array $additionalAttributes
     * @return array
     */
    public function detail($additionalAttributes = [])
    {
        $additionalAttributes = array_merge(['remote_id'], $additionalAttributes);

        // merge attributes to json
        // attributes will overwrite json if same key exists
        $detail = array_merge(
            $this->jsonArray(),
            $this->getAttributesOnly($additionalAttributes),
            [
                'db_id' => $this->id
            ]
        );

        // END
        return array_sort_recursive($detail);
    }


    /**
     * @param array $only
     * @param array $except
     * @return array
     */
    public function brief($only = [], $except = [])
    {
        // all info is based on detail
        $brief = $this->detail();

        // only and except should use either one
        // while $only get higher priority
        if (sizeof($only)) {
            $brief = array_only($brief, $only);

        } else if (sizeof($except)) {
            $brief = array_except($brief, $except);
        }

        // END
        return array_sort_recursive($brief);
    }
}