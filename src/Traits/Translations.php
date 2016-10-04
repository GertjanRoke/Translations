<?php

namespace App\Traits;

use App;
use Illuminate\Support\Str;

trait Translations
{

    /**
     * Update or create the translations
     *
     * @param  Request $request
     */
    function saveTrans($request)
    {
    	$this->saveTranslations($request);
    }


    /**
     * Update or create the translations
     *
     * @param  Request $request
     */
    function saveTranslations($request)
    {
        foreach ($request->input('trans') as $lang => $attributes) {
            $attributes['language'] = $lang;
            $trans                  = $this->translations->where('language', $lang)->first();
            if ($trans) {
                $trans->update($attributes);
            } else {
                $this->translations()->create($attributes);
            }
        }
    }

    /**
     * Get the translations for the model.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        // If the foreign key has been set, than use that key while making a relation
        if ($this->getTranslationForeignKey()) {
            return $this->hasMany($this->getFullTableName(), $this->getTranslationForeignKey());
        }

        // if the foreign key has been set and the primary key is not 'id', that use the new keys from the variables
        if ($this->getTranslationForeignKey() && $this->getLocalKey()) {
            return $this->hasMany($this->getFullTableName(), $this->getTranslationForeignKey(), $this->getLocalKey());
        }

        return $this->hasMany($this->getFullTableName());
    }

    /**
     * Returns the translations model name based on the laravel conventions
     *
     * @return string
     */
    private function getTranslationsModel()
    {
		return $this->getClassName(false) . Str::studly($this->getTableName());
    }

    /**
     * Get the class name lowercase
     *
     * @param boolean $lower
     * @return string
     */
    private function getClassName($lower = true)
    {
    	if ($lower) {
			return strtolower(class_basename($this));
    	}
		return class_basename($this);

    }

    /**
     * Get the table name
     *
     * @return string
     */
    private function getTableName()
    {
    	if (!empty(config('translations.table'))) {
			return config('translations.table');
    	}
		return 'Translations';

    }

    /**
     * Get the full namespace of the table name for the translation.
     *
     * @return string
     */
    private function getFullTableName()
    {
        if (!empty(config('translations.model_namespace'))) {
            return config('translations.model_namespace') . '\\' . $this->getTranslationsModel();
        }
        return '\App\\' . $this->getTranslationsModel();
    }

    /**
     * Return the foreign key, if set.
     * Else return null
     *
     * @return string
     */
    private function getTranslationForeignKey()
    {
        if (isset($this->transForeignKey)) {
            return $this->transForeignKey;
        }
        return null;
    }

    /**
     * Return the local key, if set.
     * Else return null
     *
     * @return string
     */
    private function getLocalKey()
    {
        if ($this->primaryKey !== 'id') {
            return $this->primaryKey;
        }
        return null;
    }

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        // If the there is no error on the translations than count them.
        // If there are translations found get the current locale that has been set
        // and check if a translation exists for the current locale.
        // Than get the requested key value from the translations table.
        if (isset($this->translations)) {
            if ($this->translations->count()) {
                $locale = strtolower(App::getLocale());
                if ($this->translations->where('locale', $locale)->count()) {
                    return $this->translations->where('locale', $locale)->first()->{$key};
                }
            }
        }
    }



    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, ['increment', 'decrement'])) {
            return call_user_func_array([$this, $method], $parameters);
        }

        // First set the variable matches using preg_match_all() that checks if the method contains uppercase letters.
        // Second check if the method starts with 'get' and check if the fourth letter is uppercase and set.
        preg_match_all('/[A-Z]/', $method, $matches, PREG_OFFSET_CAPTURE);
        if (strpos($method, 'get') === 0 && isset($matches[0][0][1])) {
            if ($matches[0][0][1] === 3) {
                // Before we can check if the attribute is set in the translations.
                // We check if there are translations for the given model.
                if ($this->translations->count()) {
                    // Get the required attribute from the method that is called.
                    // Using the substr() function to remove 'get' from the requested method and then use the helper function Str::snake() from laravel.
                    // This will change 'FilePath' to 'file_path'.
                    $attribute = Str::snake(substr($method, 3));
                    // Now set the requested translation locale based on the given locale else get the current application locale
                    if (isset($parameters[0])) {
                        $locale = $parameters[0];
                    } else {
                        $locale = App::getLocale();
                    }
                    // Try to get the translation for the given locale.
                    $translation = $this->translations->where('locale', $locale)->first();
                    // Check if a translation was found
                    if ($translation) {
                        // Check if the given attribute exists, if so return the requested value in the given locale.
                        if (isset($translation->{$attribute})) {
                            return $translation->{$attribute};
                        }
                    }
                }
            }
        }

        $query = $this->newQuery();

        return call_user_func_array([$query, $method], $parameters);
    }

}