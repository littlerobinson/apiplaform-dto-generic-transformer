<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;

/**
 * Abstract class for generic api data transformer (Transforms a DTO or an Anonymous class to a Resource object.).
 * Use it when it don't need to change entity field between entity and dto
 * Class AbstractApiDataTransformer
 * @package App\DataTransformer
 */
abstract class AbstractApiDataTransformer implements DataTransformerInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data, string $to, array $context = [])
    {
        /// Validate Asserts
        $this->validator->validate($data, $context);
        try {
            $classDto = new $to;
            foreach ($classDto as $propertyName => $propertyDefaultValue) {
                $propertyAccessorName = 'get' . ucfirst($propertyName);
                /// Check if entity property exist
                if (property_exists($data, $propertyName) && method_exists($data, $propertyAccessorName)) {
                    $classDto->{$propertyName} = $data->{$propertyAccessorName}();
                }
            }

            return $classDto;
        } catch (\Exception $e) {
            /**
             * @see inheritdoc
             * This must return the original object if no transformations have been done
             */
            return $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        try {
            /// Instanciate DTO class
            $classDto = new $to;
            /// Get the entity class name
            $entityClassName = $context['resource_class'] ?? '';

            return get_class($classDto) === $to && $data instanceof $entityClassName;
        } catch (\Exception $e) {
            return false;
        }
    }
}