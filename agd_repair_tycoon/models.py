"""Core data models for the AGD Repair Tycoon game."""

from __future__ import annotations

from dataclasses import dataclass, field
from enum import Enum
from typing import Dict, List


class ApplianceType(Enum):
    """Enumerates the appliance categories handled by the workshop."""

    WASHER = "Pralka"
    DISHWASHER = "Zmywarka"
    STOVE = "Kuchenka"
    FRIDGE = "Lodówka"

    @property
    def localized(self) -> str:
        """Return the human-friendly (Polish) name of the appliance."""

        return self.value


@dataclass
class ApplianceJob:
    """Represents a repair ticket queued for the player."""

    appliance: ApplianceType
    difficulty: int
    payout: int
    time_cost: int
    description: str
    required_skill: int

    def success_chance(self, skill_level: int) -> float:
        """Calculate the probability of success for the given skill level."""

        base_chance = 0.4 + 0.1 * (skill_level - self.required_skill)
        scaled = max(0.1, min(0.95, base_chance))
        return scaled


@dataclass
class Upgrade:
    """Represents an upgrade that can improve daily performance."""

    name: str
    cost: int
    description: str
    benefit: str


@dataclass
class Player:
    """State container for the player's progression."""

    name: str
    skills: Dict[ApplianceType, int] = field(
        default_factory=lambda: {appliance: 1 for appliance in ApplianceType}
    )
    money: int = 120
    reputation: int = 0
    energy: int = 8
    upgrades: List[Upgrade] = field(default_factory=list)

    def improve_skill(self, appliance: ApplianceType, amount: int = 1) -> None:
        self.skills[appliance] = min(10, self.skills[appliance] + amount)

    def daily_energy(self) -> int:
        """Compute available energy, factoring in relevant upgrades."""

        bonus = sum(1 for upgrade in self.upgrades if "energia" in upgrade.benefit)
        return self.energy + bonus

    def has_upgrade(self, name: str) -> bool:
        return any(upgrade.name == name for upgrade in self.upgrades)

