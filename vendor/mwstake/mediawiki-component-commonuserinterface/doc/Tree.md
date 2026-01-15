Example for creating a tree in a component like SimpleCard

class MyTree extends SimpleTreeContainer {

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'my-tree';
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$services = MediaWikiServices::getInstance();
		$treeDataGenerator = $services->get( 'MWStakeCommonUITreeDataGenerator' );

		$nodes = $treeDataGenerator->generate(
			$this->getTreeData(),
			$this->getTreeExpandPaths()
		);

		return $nodes;
	}

	/**
	 * @return array
	 */
	private function getTreeData(): array {
		return [
			[
				'id' => 'dummy-1',
				'text' => 'node-1',
				'items' => [
					[
						'id' => 'dummy-2',
						'text' => 'node-3',
						'href' => 'test',
						'items' => []
					],
					[
						'id' => 'dummy-3',
						'text' => 'node-4',
						'items' => [
							[
								'id' => 'dummy-4',
								'text' => 'node-5',
								'items' => []
							]
						]
					]
				]
			],
			[
				'id' => 'dummy-5',
				'text' => 'node-2',
				'items' => [
					[
						'id' => 'dummy-6',
						'text' => 'node-6',
						'items' => [
							[
								'id' => 'dummy-7',
								'text' => 'node-7',
								'items' => [
										[
										'id' => 'dummy-8',
										'text' => 'node-8',
										'items' => []
									]
								]
							]
						]
					]
				]
			]
		];
	}

	/**
	 * @return array
	 */
	protected function getExpandPaths(): array {
		return [
			'dummy-1/dummy-3',
			'dummy-5',
		];
	}
}