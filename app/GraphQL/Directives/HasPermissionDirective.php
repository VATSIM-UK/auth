<?php


namespace App\GraphQL\Directives;


use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Access\Gate;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class HasPermissionDirective extends BaseDirective implements FieldMiddleware
{

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * CanDirective constructor.
     * @param Gate $gate
     * @return void
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    /**
     * Ensure the user is authorized to access this field.
     *
     * @param FieldValue $fieldValue
     * @param Closure $next
     * @return FieldValue
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $previousResolver = $fieldValue->getResolver();

        return $next(
            $fieldValue->setResolver(
                function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($previousResolver) {
                    $gate = $this->gate->forUser($context->user());
                    $ability = $this->directiveArgValue('permission');

                    $this->authorize($gate, $ability);

                    return $previousResolver($root, $args, $context, $resolveInfo);
                }
            )
        );
    }

    /**
     * @param Gate $gate
     * @param string|string[] $ability
     * @return void
     *
     * @throws AuthorizationException
     */
    protected function authorize(Gate $gate, $ability): void
    {
        if (! $gate->check($ability)) {
            throw new AuthorizationException(
                "You are not authorized to perform {$this->nodeName()}"
            );
        }
    }
}
