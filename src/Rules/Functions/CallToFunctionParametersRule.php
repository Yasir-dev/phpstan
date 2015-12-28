<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\FunctionCallParametersCheck;

class CallToFunctionParametersRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\FunctionCallParametersCheck
	 */
	private $check;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Rules\FunctionCallParametersCheck $check
	 */
	public function __construct(Broker $broker, FunctionCallParametersCheck $check)
	{
		$this->broker = $broker;
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!($node->name instanceof \PhpParser\Node\Name)) {
			return [];
		}
		$name = (string) $node->name;

		$function = $this->getFunction($scope, $name);
		if ($function === false) {
			return [];
		}

		return $this->check->check(
			$function,
			$node,
			[
				'Function ' . $name . ' invoked with %d parameter, %d required.',
				'Function ' . $name . ' invoked with %d parameters, %d required.',
				'Function ' . $name . ' invoked with %d parameter, at least %d required.',
				'Function ' . $name . ' invoked with %d parameters, at least %d required.',
				'Function ' . $name . ' invoked with %d parameter, %d-%d required.',
				'Function ' . $name . ' invoked with %d parameters, %d-%d required.',
			]
		);
	}

	private function getFunction(Scope $scope, $name)
	{
		if ($scope->getNamespace() !== null) {
			$namespacedName = sprintf('%s\\%s', $scope->getNamespace(), $name);
			if ($this->broker->hasFunction($namespacedName)) {
				return $this->broker->getFunction($namespacedName);
			}
		}

		if ($this->broker->hasFunction($name)) {
			return $this->broker->getFunction($name);
		}

		return false;
	}

}
