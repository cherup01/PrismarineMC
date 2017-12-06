<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Slab extends Transparent{

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	protected function recalculateBoundingBox(){
		if($this->x === null || $this->y === null || $this->z === null){
			return null;
		}
		if(($this->meta & 0x08) > 0){
			return new AxisAlignedBB(
				$this->x,
				$this->y + 0.5,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		}else{
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + 0.5,
				$this->z + 1
			);
		}
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		if(parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock)){
			return true;
		}

		if($blockReplace->getId() === $this->getId() and ($blockReplace->getDamage() & 0x07) === $this->meta){
			if(($blockReplace->getDamage() & 0x08) !== 0){ //Trying to combine with top slab
				return $clickVector->y <= 0.5 or (!$isClickedBlock and $face === Vector3::SIDE_UP);
			}else{
				return $clickVector->y >= 0.5 or (!$isClickedBlock and $face === Vector3::SIDE_DOWN);
			}
		}

		return false;
 	}
 
	public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facePos, Player $player = null) : bool{
		$this->meta &= 0x07;
		if($face === Vector3::SIDE_DOWN){
			if($target->getId() === $this->id and ($target->getDamage() & 0x08) === 0x08 and ($target->getDamage() & 0x07) === $this->meta){
				$this->getLevel()->setBlock($target, BlockFactory::get($this->doubleId, $this->meta), true);

				return true;
			}elseif($block->getId() === $this->id and ($block->getDamage() & 0x07) === $this->meta){
				$this->getLevel()->setBlock($block, BlockFactory::get($this->doubleId, $this->meta), true);

				return true;
			}else{
				$this->meta |= 0x08;
			}
		}elseif($face === Vector3::SIDE_UP){
			if($target->getId() === $this->id and ($target->getDamage() & 0x08) === 0 and ($target->getDamage() & 0x07) === $this->meta){
				$this->getLevel()->setBlock($target, BlockFactory::get($this->doubleId, $this->meta), true);

				return true;
			}elseif($block->getId() === $this->id and ($block->getDamage() & 0x07) === $this->meta){
				$this->getLevel()->setBlock($block, BlockFactory::get($this->doubleId, $this->meta), true);

				return true;
			}
		}else{ //TODO: collision
			if($block->getId() === $this->id){
				if(($block->getDamage() & 0x07) === $this->meta){
					$this->getLevel()->setBlock($block, BlockFactory::get($this->doubleId, $this->meta), true);

					return true;
				}

				return false;
			}else{
				if($facePos->y > 0.5){
					$this->meta |= 0x08;
				}
			}
		}

		if($block->getId() === $this->id and ($target->getDamage() & 0x07) !== ($this->meta & 0x07)){
			return false;
		}
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}
}