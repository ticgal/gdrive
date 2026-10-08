<?php

declare(strict_types=1);

namespace CustomPHPStanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Detects if a class in Config.php or config.class.php has the $rightname attribute with the value 'config'.
 */
class PermissionControlRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class; // Analyzes class declarations.
    }

    /**
     * @param Class_ $node
     * @param Scope $scope
     * @return array
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Get the file path being analyzed
        $filePath = $scope->getFile();

        $class_rightname = [
            'Config'        => 'config',
            'Cron'          => 'config',
            'Profile'       => 'profile',
            'Entity'        => 'entity',
            'Notification'  => 'notification',
        ];

        $errors = [];
        foreach ($class_rightname as $class_file => $rightname_value) {
            $ns_class = $class_file . '.php';
            $legacy_class = strtolower($class_file) . '.class.php';
            // Apply the rule only to Config.php or config.class.php
            if (!preg_match('/(' . preg_quote($ns_class, '/') . '|' . preg_quote($legacy_class, '/') . ')$/i', $filePath)) {
                continue; // Skip files that don't match
            }

            $has_property = false;
            // Check if the class has a property named $rightname
            foreach ($node->getProperties() as $property) {
                if ($property instanceof Property && $property->props[0]->name->toString() === 'rightname') {
                    $has_property = true;
                    $default = $property->props[0]->default;

                    // Check if the default value of $rightname is 'config'
                    if (!$default instanceof String_ || $default->value != $rightname_value) {
                        $errors[] = RuleErrorBuilder::message(
                            "The class in $ns_class or $legacy_class must have the \$rightname attribute set to \"$rightname_value\".",
                        )->build();
                    }
                }
            }

            // If $rightname is not defined, report an error
            if (!$has_property) {
                $errors[] = RuleErrorBuilder::message(
                    "The class in $ns_class or $legacy_class must define the \$rightname attribute with the value \"$rightname_value\".",
                )->build();
            }
        }

        return $errors;
    }
}
