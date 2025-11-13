"""Text-based implementation of the AGD Repair Tycoon management game."""

from __future__ import annotations

import random
from dataclasses import dataclass
from typing import List, Optional

from .models import ApplianceJob, ApplianceType, Player, Upgrade


@dataclass
class DaySummary:
    """Aggregated report for a finished day."""

    completed: int
    failed: int
    profit: int
    reputation_change: int


JOB_DESCRIPTIONS = {
    ApplianceType.WASHER: [
        "Wymiana zużytego łożyska w pralce", "Diagnoza błędu F05", "Czyszczenie pompy odpływowej",
    ],
    ApplianceType.DISHWASHER: [
        "Uszczelnienie drzwi zmywarki", "Czyszczenie ramion spryskujących", "Naprawa modułu grzewczego",
    ],
    ApplianceType.STOVE: [
        "Kalibracja piekarnika gazowego", "Wymiana zapalarki", "Izolacja termiczna piekarnika",
    ],
    ApplianceType.FRIDGE: [
        "Uzupełnienie czynnika chłodzącego", "Wymiana termostatu", "Odblokowanie odpływu skroplin",
    ],
}

UPGRADES = [
    Upgrade(
        name="Mobilny magazyn",
        cost=180,
        description="Masz pod ręką większość części zamiennych",
        benefit="+10% skuteczności napraw dzięki lepszym częściom",
    ),
    Upgrade(
        name="Dodatkowy serwisant",
        cost=240,
        description="Doświadczenie kolegi skraca czas napraw",
        benefit="energia +1 każdego dnia",
    ),
    Upgrade(
        name="Portal opinii",
        cost=150,
        description="Klienci częściej polecają firmę",
        benefit="+1 reputacji na zakończenie dnia",
    ),
]


class Game:
    """Core game engine orchestrating the simulation loop."""

    def __init__(self, player_name: str, days: int = 10, seed: Optional[int] = None) -> None:
        self.player = Player(player_name)
        self.total_days = days
        self.random = random.Random(seed)
        self.current_day = 1

    # Public API ---------------------------------------------------------
    def run(self) -> None:
        self._display_intro()
        while self.current_day <= self.total_days and self.player.money > -100:
            summary = self._run_day()
            self._print_day_summary(summary)
            self.current_day += 1

        self._display_outro()

    # Helper methods ----------------------------------------------------
    def _run_day(self) -> DaySummary:
        print(f"\n===== Dzień {self.current_day} =====")
        energy = self.player.daily_energy()
        jobs = self._generate_jobs()
        completed = 0
        failed = 0
        profit = 0
        reputation = 0

        while energy > 0 and jobs:
            self._print_status(energy, jobs)
            raw_choice = self._safe_input(
                "Wybierz zadanie (numer), [O]dpoczynek, [U]lepszenia, [Z]akończ dzień: "
            )
            if raw_choice is None:
                return DaySummary(completed, failed, profit, reputation)
            choice = raw_choice.strip()
            if not choice:
                continue
            if choice.lower() == "z":
                break
            if choice.lower() == "o":
                print("\nRobisz krótką przerwę. Energia +1.")
                energy = min(energy + 1, self.player.daily_energy())
                continue
            if choice.lower() == "u":
                self._offer_upgrades()
                continue

            if not choice.isdigit():
                print("Nie rozpoznano wyboru. Spróbuj ponownie.")
                continue

            index = int(choice) - 1
            if index < 0 or index >= len(jobs):
                print("Wybrane zadanie nie istnieje.")
                continue

            job = jobs.pop(index)
            energy_cost = max(1, job.time_cost - (1 if self.player.has_upgrade("Dodatkowy serwisant") else 0))
            if energy_cost > energy:
                print("Brakuje energii na to zadanie. Wybierz inne lub zakończ dzień.")
                jobs.insert(index, job)
                continue

            energy -= energy_cost
            success = self._attempt_job(job)
            if success:
                completed += 1
                self.player.money += job.payout
                profit += job.payout
                rep_gain = 2 if job.difficulty >= 3 else 1
                reputation += rep_gain
                self.player.reputation += rep_gain
                self.player.improve_skill(job.appliance)
                print(
                    f"\nSukces! Klient płaci {job.payout} zł, reputacja +{rep_gain}."
                    f" Doświadczenie z kategorią {job.appliance.localized} rośnie."
                )
            else:
                failed += 1
                penalty = job.payout // 2
                self.player.money -= penalty
                reputation -= 1
                self.player.reputation = max(0, self.player.reputation - 1)
                print(
                    f"\nNiepowodzenie. Musisz odkupić części za {penalty} zł i reputacja spada."
                )

        # End of day bonuses
        if self.player.has_upgrade("Portal opinii"):
            reputation += 1
            self.player.reputation += 1
            print("Twoi klienci zostawiają pozytywne opinie! Reputacja +1.")

        return DaySummary(completed, failed, profit, reputation)

    def _generate_jobs(self) -> List[ApplianceJob]:
        amount = self.random.randint(3, 5)
        jobs: List[ApplianceJob] = []
        for _ in range(amount):
            appliance = self.random.choice(list(ApplianceType))
            difficulty = self.random.randint(1, 5)
            time_cost = self.random.randint(1, 3)
            base_payout = 60 + difficulty * 30
            description = self.random.choice(JOB_DESCRIPTIONS[appliance])
            job = ApplianceJob(
                appliance=appliance,
                difficulty=difficulty,
                payout=base_payout + self.random.randint(-20, 30),
                time_cost=time_cost,
                description=description,
                required_skill=max(1, difficulty - 1),
            )
            jobs.append(job)
        return jobs

    def _attempt_job(self, job: ApplianceJob) -> bool:
        skill = self.player.skills[job.appliance]
        success_probability = job.success_chance(skill)
        # Mobilny magazyn upgrade improves success slightly
        if self.player.has_upgrade("Mobilny magazyn"):
            success_probability = min(0.98, success_probability + 0.1)

        roll = self.random.random()
        return roll <= success_probability

    def _offer_upgrades(self) -> None:
        affordable_upgrades = [u for u in UPGRADES if not self.player.has_upgrade(u.name)]
        if not affordable_upgrades:
            print("\nKupiono już wszystkie dostępne ulepszenia.")
            return

        print("\n--- Sklep ulepszeń ---")
        for idx, upgrade in enumerate(affordable_upgrades, start=1):
            print(f"{idx}. {upgrade.name} ({upgrade.cost} zł) - {upgrade.description}")
            print(f"   Korzyść: {upgrade.benefit}")

        raw_choice = self._safe_input("Wybierz ulepszenie do zakupu lub wciśnij Enter, aby wrócić: ")
        if raw_choice is None:
            return
        choice = raw_choice.strip()
        if not choice:
            return
        if not choice.isdigit():
            print("Niepoprawny wybór.")
            return

        index = int(choice) - 1
        if index < 0 or index >= len(affordable_upgrades):
            print("Takie ulepszenie nie istnieje.")
            return

        upgrade = affordable_upgrades[index]
        if self.player.money < upgrade.cost:
            print("Brak wystarczających środków.")
            return

        self.player.money -= upgrade.cost
        self.player.upgrades.append(upgrade)
        print(f"Zakupiono ulepszenie: {upgrade.name}!")

    def _safe_input(self, prompt: str) -> Optional[str]:
        try:
            return input(prompt)
        except EOFError:
            print("\nBrak dodatkowych danych wejściowych. Kończymy grę.")
            self.current_day = self.total_days + 1
            return None

    def _print_status(self, energy: int, jobs: List[ApplianceJob]) -> None:
        print("\n--- Status warsztatu ---")
        print(f"Imię serwisanta: {self.player.name}")
        print(f"Środki: {self.player.money} zł | Reputacja: {self.player.reputation} | Energia: {energy}")
        print("Umiejętności: " + ", ".join(
            f"{appliance.localized} {self.player.skills[appliance]}" for appliance in ApplianceType
        ))
        print("\nZadania dostępne dziś:")
        for idx, job in enumerate(jobs, start=1):
            print(
                f"{idx}. {job.description} ({job.appliance.localized}) -"
                f" trudność {job.difficulty}, czas {job.time_cost}, zapłata {job.payout} zł"
            )

    def _print_day_summary(self, summary: DaySummary) -> None:
        print("\nPodsumowanie dnia:")
        print(f"Ukończone naprawy: {summary.completed}")
        print(f"Nieudane zlecenia: {summary.failed}")
        print(f"Dzisiejszy wynik finansowy: {summary.profit} zł")
        print(f"Zmiana reputacji: {summary.reputation_change}")

    def _display_intro(self) -> None:
        print("""
========================================
        AGD REPAIR TYCOON ONLINE
========================================
Twoim zadaniem jest poprowadzić serwis naprawczy pralek, zmywarek, kuchenek i lodówek.
Zdobądź reputację, inwestuj w ulepszenia i stań się najlepszym fachowcem w mieście!
        """)

    def _display_outro(self) -> None:
        print("""
========================================
        KONIEC DEMO
========================================
Dziękujemy za grę! Czy udało Ci się rozwinąć serwis AGD?
Spróbuj ponownie z innymi decyzjami, aby odkryć nowe kombinacje ulepszeń.
        """)


def main() -> None:
    """Entry-point for launching the game from the command line."""

    print("Witaj w AGD Repair Tycoon!")
    try:
        name = input("Jak nazywa się Twój serwisant? (Pozostaw puste dla 'Alex'): ").strip()
    except EOFError:
        name = "Alex"
        print("\nNie podano imienia. Użyto domyślnego: Alex")
    if not name:
        name = "Alex"
    game = Game(name)
    game.run()


if __name__ == "__main__":  # pragma: no cover - entry point for script execution
    main()
