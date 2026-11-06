<?php

declare(strict_types=1);

namespace CustomPHPStanRules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node\Expr\StaticCall;

/**
 * Detects the use of Toolbox::logInFile().
 */
class DebugLogsRule implements Rule
{
    private const SUSPICIOUS_FLAGS = [
        '-debug',
        '-test',
        '-tmp',
        '-temp',
        '-trace',
        '-demo',
    ];

    public function getNodeType(): string
    {
        return StaticCall::class; // Analyzes static method calls.
    }

    /**
     * @param Node $node
     * @param Scope $scope
     * @return array
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            $node instanceof Node\Expr\StaticCall &&
            $node->class instanceof Node\Name &&
            $node->name instanceof Node\Identifier
        ) {
            $className = (string) $node->class;
            $methodName = (string) $node->name;

            if ($className === 'Toolbox' && $methodName === 'logInFile') {
                // check parameter 0 (filename) for suspicious flags
                if (isset($node->args[0])) {
                    $firstArg = $node->args[0]->value;

                    if ($firstArg instanceof Node\Scalar\String_) {
                        $filename = $firstArg->value;
                        foreach (self::SUSPICIOUS_FLAGS as $flag) {
                            if (str_contains($filename, $flag)) {
                                return [
                                    RuleErrorBuilder::message(
                                        sprintf(
                                            'The use of Toolbox::logInFile() with suspicious flag "%s" in filename is prohibited by this rule.',
                                            $flag,
                                        ),
                                    )->build(),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return [];
    }
}
