<?php

namespace BlueSpice\Discovery\AttentionIndicator;

use BlueSpice\Discovery\AttentionIndicator;

class NULLIndicator extends AttentionIndicator {

	protected function doIndicationCount(): int {
		return 0;
	}

}
