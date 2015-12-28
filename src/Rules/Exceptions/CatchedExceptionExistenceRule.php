<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class CatchedExceptionExistenceRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 */
	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return Catch_::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$class = (string) $node->type;

		if (!$this->broker->hasClass($class)) {
			return [
				sprintf('Catched class %s does not exist.', $class),
			];
		}

		$classReflection = $this->broker->getClass($class);
		if (!$classReflection->isInterface() && $class !== 'Exception' && !$classReflection->isSubclassOf('Exception')) {
			return [
				sprintf('Catched class %s is not an exception.', $class),
			];
		}

		return [];
	}

}
