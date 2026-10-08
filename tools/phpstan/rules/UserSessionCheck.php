<?php

declare(strict_types=1);

namespace CustomPHPStanRules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class UserSessionCheck implements Rule
{
    private static $checkedFiles = [];

    public function getNodeType(): string
    {
        return Expression::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $message_errors = [];
        $filePath = $scope->getFile();
        if (!preg_match('#/(ajax|front)/#', $filePath)) {
            return [];
        }

        if (isset(self::$checkedFiles[$filePath])) {
            return [];
        }
        self::$checkedFiles[$filePath] = true;
        $file_content = file_get_contents($filePath);

        // Check plugin activation
        $pluginCheck = 'Plugin::isPluginActive';
        if (strpos($file_content, $pluginCheck) === false) {
            $message_errors[] = RuleErrorBuilder::message(
                sprintf(
                    '!! Unprotected page !! The check for plugin activation using %s is missing.',
                    $pluginCheck,
                ),
            )->build();
        }

        // Check session verification functions
        $session_functions = [
            'Session::checkLoginUser',
            'Session::checkRight',
            'Session::checkCentralAccess',
            'Session::checkHelpdeskAccess',
            'Session::validateIDOR',
        ];

        $found = false;
        foreach ($session_functions as $function) {
            if (strpos($file_content, $function) !== false) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $message_errors[] = RuleErrorBuilder::message(
                sprintf(
                    '!! Unprotected page !! The use of user session verification functions (%s) has not been detected.',
                    implode(', ', $session_functions),
                ),
            )->build();
        }

        return $message_errors;
    }
}
