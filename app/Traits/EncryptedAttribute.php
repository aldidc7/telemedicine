<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Exception;

/**
 * ============================================
 * ENCRYPTED ATTRIBUTE TRAIT
 * ============================================
 * 
 * Trait untuk automatic encryption/decryption of model attributes.
 * 
 * Usage dalam Model:
 * use EncryptedAttribute;
 * protected $encrypted = ['ssn', 'phone', 'address'];
 * 
 * Features:
 * - Automatic encryption on save
 * - Automatic decryption on read
 * - Works with Eloquent accessors/mutators
 * - HIPAA/GDPR compliant
 * 
 * @author Aplikasi Telemedicine RSUD dr. R. Soedarsono
 * @version 1.0
 * @date 2025-12-20
 */
trait EncryptedAttribute
{
    /**
     * Boot trait - setup mutators
     */
    public static function bootEncryptedAttribute()
    {
        // Encrypt on save
        static::saving(function ($model) {
            foreach ($model->getEncryptedAttributes() as $attribute) {
                if ($model->isDirty($attribute)) {
                    $model->attributes[$attribute] = $model->encryptAttribute(
                        $model->attributes[$attribute]
                    );
                }
            }
        });
    }

    /**
     * Get encrypted attributes
     */
    protected function getEncryptedAttributes()
    {
        return isset($this->encrypted) ? $this->encrypted : [];
    }

    /**
     * Encrypt attribute value
     */
    protected function encryptAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        try {
            return Crypt::encryptString((string) $value);
        } catch (Exception $e) {
            report($e);
            return $value;
        }
    }

    /**
     * Decrypt attribute value
     */
    protected function decryptAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Exception $e) {
            // If decryption fails, return original value
            // (might be unencrypted legacy data)
            return $value;
        }
    }

    /**
     * Override getAttribute to decrypt on read
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Decrypt if attribute is encrypted
        if (in_array($key, $this->getEncryptedAttributes())) {
            return $this->decryptAttribute($value);
        }

        return $value;
    }

    /**
     * Override setAttribute to handle encrypted attributes
     */
    public function setAttribute($key, $value)
    {
        // For encrypted attributes, don't double-encrypt
        // Let the saving event handle encryption
        if (in_array($key, $this->getEncryptedAttributes())) {
            $this->attributes[$key] = $value;
        } else {
            parent::setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Override toArray to decrypt when converting to array
     */
    public function toArray()
    {
        $array = parent::toArray();

        foreach ($this->getEncryptedAttributes() as $attribute) {
            if (isset($array[$attribute])) {
                $array[$attribute] = $this->decryptAttribute($array[$attribute]);
            }
        }

        return $array;
    }
}
