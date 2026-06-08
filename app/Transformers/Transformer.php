<?php

declare(strict_types=1);

namespace App\Transformers;

use Illuminate\Http\Request;

abstract class Transformer
{
    /**
     * Relations to always embed in the output.
     *
     * @var list<string>
     */
    protected array $defaultIncludes = [];

    /**
     * Relations that can be requested but are not embedded by default.
     *
     * @var list<string>
     */
    protected array $availableIncludes = [];

    /**
     * Returns the base data for the given model (without includes).
     *
     * @return array<string, mixed>
     */
    abstract public function transform(mixed $model): array;

    /**
     * Eloquent relations to eager-load when this transformer is used in a DataTable query.
     *
     * @return list<string>
     */
    abstract public static function eagerLoads(): array;

    /**
     * Assembles base data from transform() and merges all active includes.
     *
     * @return array<string, mixed>
     */
    final public function toArray(mixed $model, Request $request): array
    {
        $data = $this->transform($model);

        foreach ($this->defaultIncludes as $relation) {
            $method = 'include'.ucfirst($relation);

            if (method_exists($this, $method)) {
                $data[$relation] = $this->{$method}($model, $request);
            }
        }

        return $data;
    }
}
