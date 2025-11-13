const CLIENT_NAMES = [
    'Anna', 'Bartek', 'Celina', 'Damian', 'Ewelina', 'Filip', 'Grażyna', 'Hubert', 'Iwona', 'Jan',
    'Karolina', 'Łukasz', 'Magda', 'Norbert', 'Olga', 'Patryk', 'Roksana', 'Stefan', 'Teresa', 'Wojtek'
];

const APPLIANCES = [
    {
        id: 'washer',
        name: 'Pralka',
        description: 'Specjalizujemy się w naprawach wycieków, głośnych łożysk i usterek panelu sterowania.',
        scenarios: [
            {
                issue: 'Wyciek spod obudowy podczas wirowania',
                difficulty: 1,
                steps: [
                    {
                        phase: 'Diagnoza',
                        question: 'Co w pierwszej kolejności sprawdzisz, aby zlokalizować źródło wycieku?',
                        options: [
                            'Wyjmę bęben i obejrzę amortyzatory',
                            'Zdemontuję tylną pokrywę i sprawdzę węże odpływowe',
                            'Zresetuję elektronikę odłączając pralkę na 10 minut'
                        ],
                        answerIndex: 1,
                        successText: 'Wąż odpływowy był poluzowany – zakręcasz obejmę i uszczelniasz połączenie.'
                    },
                    {
                        phase: 'Naprawa',
                        question: 'Jak zabezpieczysz połączenie, aby nie rozszczelniło się ponownie?',
                        options: [
                            'Nałożę opaskę zaciskową i silikon wysokotemperaturowy',
                            'Podgrzeję miejsce opalarką, by plastik dopasował się do króćca',
                            'Uszczelnię taśmą izolacyjną i dodatkową warstwą kleju'
                        ],
                        answerIndex: 0,
                        successText: 'Nowa opaska i silikon wysokotemperaturowy gwarantują pełną szczelność układu.'
                    },
                    {
                        phase: 'Testy',
                        question: 'Jak sprawdzisz, czy naprawa się udała?',
                        options: [
                            'Uruchomię szybki program płukania na pełnym wsadzie',
                            'Wleję wodę ręcznie i zostawię pralkę na 30 minut',
                            'Uruchomię pralkę bez wsadu na programie testowym serwisowym'
                        ],
                        answerIndex: 2,
                        successText: 'Tryb serwisowy potwierdza brak wycieków – klient jest zachwycony.'
                    }
                ]
            },
            {
                issue: 'Błąd E21 – silnik nie startuje',
                difficulty: 2,
                steps: [
                    {
                        phase: 'Diagnoza',
                        question: 'Co będzie najszybszym sposobem, aby zweryfikować stan szczotek?',
                        options: [
                            'Pomiar rezystancji uzwojeń na module',
                            'Demontaż silnika i inspekcja szczotek węglowych',
                            'Podmiana całego modułu sterującego na nowy'
                        ],
                        answerIndex: 1,
                        successText: 'Zużyte szczotki to klasyka – wymieniasz je w 15 minut.'
                    },
                    {
                        phase: 'Naprawa',
                        question: 'Jak zwiększysz szansę na bezawaryjną pracę po naprawie?',
                        options: [
                            'Zamontuję oryginalne szczotki i doczyszczę komutator',
                            'Nałożę dodatkową warstwę smaru na komutator',
                            'Wzmocnię mocowania silnika metalowymi wspornikami'
                        ],
                        answerIndex: 0,
                        successText: 'Po czyszczeniu i wymianie szczotek silnik działa jak nowy.'
                    },
                    {
                        phase: 'Testy',
                        question: 'Jak przetestujesz naprawę, by upewnić się, że błąd nie wróci?',
                        options: [
                            'Uruchomię tryb serwisowy z pełnym cyklem wirowania',
                            'Włączę tryb ECO z pustym bębnem',
                            'Sprawdzę logi błędów na module i od razu oddam sprzęt'
                        ],
                        answerIndex: 0,
                        successText: 'Pełne wirowanie przebiegło bez problemów – reputacja rośnie!'
                    }
                ]
            }
        ]
    },
    {
        id: 'dishwasher',
        name: 'Zmywarka',
        description: 'Naprawiamy pompy, układy grzewcze i moduły sterujące w zmywarkach.',
        scenarios: [
            {
                issue: 'Program zatrzymuje się na płukaniu, naczynia brudne',
                difficulty: 2,
                steps: [
                    {
                        phase: 'Diagnoza',
                        question: 'Co najpierw sprawdzisz, aby zdiagnozować problem?',
                        options: [
                            'Filtr i sitko poboru wody',
                            'Zawór trójdrożny oraz przepływomierz',
                            'Szczelność ramion spryskujących'
                        ],
                        answerIndex: 1,
                        successText: 'Przepływomierz zablokowany resztkami – wymiana to formalność.'
                    },
                    {
                        phase: 'Naprawa',
                        question: 'Jakie działanie zapewni trwałe rozwiązanie?',
                        options: [
                            'Wyczyszczę przepływomierz i dokonam kalibracji',
                            'Zamontuję nowy przepływomierz i zaktualizuję firmware',
                            'Podmienię pompę myjącą na mocniejszy model'
                        ],
                        answerIndex: 1,
                        successText: 'Nowy przepływomierz i aktualizacja oprogramowania rozwiązują problem na stałe.'
                    },
                    {
                        phase: 'Testy',
                        question: 'Jak potwierdzisz skuteczność naprawy?',
                        options: [
                            'Uruchomię program automatyczny z tabletką serwisową',
                            'Poproszę klienta o potwierdzenie po tygodniu użytkowania',
                            'Sprawdzę czy świeci kontrolka soli i zakończę wizytę'
                        ],
                        answerIndex: 0,
                        successText: 'Program automatyczny potwierdza perfekcyjnie czyste naczynia.'
                    }
                ]
            }
        ]
    },
    {
        id: 'oven',
        name: 'Kuchenka',
        description: 'Usprawniamy piekarniki, płyty indukcyjne i moduły sterujące.',
        scenarios: [
            {
                issue: 'Płyta indukcyjna wyrzuca zabezpieczenia po kilku minutach',
                difficulty: 3,
                steps: [
                    {
                        phase: 'Diagnoza',
                        question: 'Jaki pomiar wykonasz, by znaleźć przyczynę?',
                        options: [
                            'Sprawdzę stan wentylatora i filtrów powietrza',
                            'Zmierzam rezystancję cewek i kondensatorów mocy',
                            'Sprawdzę ciągłość przewodu ochronnego PE'
                        ],
                        answerIndex: 1,
                        successText: 'Przepalony kondensator mocy powodował przeciążenia – czas na wymianę.'
                    },
                    {
                        phase: 'Naprawa',
                        question: 'Co zrobisz, aby wyeliminować ryzyko ponownej awarii?',
                        options: [
                            'Zamontuję kondensatory wysokotemperaturowe i wymienię pastę termiczną',
                            'Zamontuję dodatkowy wentylator chłodzący obudowę',
                            'Wymienię cały moduł mocy na wersję z poprzedniego rocznika'
                        ],
                        answerIndex: 0,
                        successText: 'Wysokotemperaturowe kondensatory i świeża pasta termiczna stabilizują układ.'
                    },
                    {
                        phase: 'Testy',
                        question: 'Jak przeprowadzisz test obciążeniowy?',
                        options: [
                            'Włączę wszystkie pola na 70% mocy przez 10 minut',
                            'Wykorzystam tryb boost na jednym polu przez 2 minuty',
                            'Włączę jedno pole i sprawdzę temperaturę obudowy'
                        ],
                        answerIndex: 0,
                        successText: 'Test obciążeniowy przechodzi bez problemów – klient zamawia abonament serwisowy.'
                    }
                ]
            }
        ]
    },
    {
        id: 'fridge',
        name: 'Lodówka',
        description: 'Diagnozujemy problemy z chłodzeniem, elektroniką i uszczelnieniami.',
        scenarios: [
            {
                issue: 'Lodówka głośno pracuje i nie osiąga zadanej temperatury',
                difficulty: 2,
                steps: [
                    {
                        phase: 'Diagnoza',
                        question: 'Co sprawdzisz w pierwszej kolejności?',
                        options: [
                            'Wentylatory oraz drożność kanałów powietrznych',
                            'Grzałkę odszraniania i czujniki temperatury',
                            'Uszczelkę drzwiową i ustawienie lodówki'
                        ],
                        answerIndex: 0,
                        successText: 'Zablokowany wentylator parownika – szybka naprawa przywraca cichą pracę.'
                    },
                    {
                        phase: 'Naprawa',
                        question: 'Jak zapewnisz prawidłowy przepływ powietrza w przyszłości?',
                        options: [
                            'Zastosuję silikonowe dystanse między półkami',
                            'Wymienię wentylator i dodam filtry przeciwpyłowe',
                            'Zalecę klientowi częstsze rozmrażanie'
                        ],
                        answerIndex: 1,
                        successText: 'Nowy wentylator i filtry eliminują hałas i poprawiają cyrkulację.'
                    },
                    {
                        phase: 'Testy',
                        question: 'Jak sprawdzisz stabilność temperatury po naprawie?',
                        options: [
                            'Podłączę rejestrator temperatury na 30 minut',
                            'Dotknę wylotu powietrza i ocenię subiektywnie',
                            'Zamknę drzwi i zapytam klienta za tydzień'
                        ],
                        answerIndex: 0,
                        successText: 'Rejestrator temperatury potwierdza stabilną pracę urządzenia.'
                    }
                ]
            }
        ]
    }
];

const UPGRADES = [
    {
        id: 'toolkit',
        name: 'Mobilny zestaw narzędzi premium',
        description: 'Zmniejsza zużycie energii o 10% na każdy etap naprawy.',
        cost: 1400,
        effect: state => {
            state.energyModifier = Math.max(0.6, state.energyModifier - 0.1);
        }
    },
    {
        id: 'crm',
        name: 'Platforma CRM dla serwisu',
        description: 'Zwiększa reputację po każdej udanej naprawie o dodatkowe 0.25 punktu.',
        cost: 2200,
        effect: state => {
            state.reputationBonus += 0.25;
        }
    },
    {
        id: 'fleet',
        name: 'Flota samochodów serwisowych',
        description: 'Pozwala przyjąć o jedno dodatkowe zlecenie każdego dnia.',
        cost: 3200,
        effect: state => {
            state.maxConcurrentJobs += 1;
        }
    },
    {
        id: 'academy',
        name: 'Akademia szkoleniowa',
        description: 'Premia +10% do wynagrodzenia za każde ukończone zlecenie.',
        cost: 4100,
        effect: state => {
            state.rewardModifier += 0.1;
        }
    }
];

const STEP_ENERGY_COST = 18;
const LEADERBOARD_KEY = 'agd_repair_tycoon_leaderboard';
const PLAYER_KEY = 'agd_repair_tycoon_player';

function randomItem(array) {
    return array[Math.floor(Math.random() * array.length)];
}

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('pl-PL', {
        style: 'currency',
        currency: 'PLN',
        maximumFractionDigits: 0
    }).format(Math.round(value));
}

function createId(prefix) {
    return `${prefix}-${Math.random().toString(36).slice(2, 9)}-${Date.now()}`;
}

class GameState {
    constructor(playerName) {
        this.playerId = this.restorePlayerId();
        this.playerName = playerName || 'Serwisant';
        this.day = 0;
        this.coins = 2400;
        this.reputation = 1;
        this.availableJobs = [];
        this.activeJobs = [];
        this.completedToday = 0;
        this.maxConcurrentJobs = 2;
        this.maxEnergy = 100;
        this.energy = this.maxEnergy;
        this.energyModifier = 1;
        this.rewardModifier = 0;
        this.reputationBonus = 0;
        this.purchasedUpgrades = new Set();
    }

    restorePlayerId() {
        const saved = localStorage.getItem(`${PLAYER_KEY}_id`);
        if (saved) return saved;
        const id = createId('player');
        localStorage.setItem(`${PLAYER_KEY}_id`, id);
        return id;
    }

    startDay() {
        this.day += 1;
        this.energy = this.maxEnergy;
        this.completedToday = 0;
        this.availableJobs = this.generateJobs();
        this.activeJobs = [];
        return this.availableJobs;
    }

    generateJobs() {
        const jobCount = Math.min(5, 3 + Math.floor(this.reputation));
        const jobs = [];
        for (let i = 0; i < jobCount; i += 1) {
            const appliance = randomItem(APPLIANCES);
            const scenario = randomItem(appliance.scenarios);
            jobs.push({
                id: createId('job'),
                client: randomItem(CLIENT_NAMES),
                appliance: appliance.name,
                applianceId: appliance.id,
                scenario,
                status: 'available',
                stepIndex: 0,
                reward: this.calculateReward(scenario),
                reputationGain: this.calculateReputationGain(scenario.difficulty),
                energyCost: scenario.steps.length * STEP_ENERGY_COST,
                createdAt: Date.now()
            });
        }
        return jobs;
    }

    calculateReward(scenario) {
        const base = 400 + scenario.difficulty * 320;
        const variance = Math.random() * 180;
        const modifier = 1 + this.rewardModifier;
        return Math.round((base + variance) * modifier);
    }

    calculateReputationGain(difficulty) {
        return 0.6 + difficulty * 0.2 + this.reputationBonus;
    }

    canAcceptJob(job) {
        if (this.activeJobs.length >= this.maxConcurrentJobs) return false;
        if (this.energy < STEP_ENERGY_COST) return false;
        return job.status === 'available';
    }

    acceptJob(jobId) {
        const job = this.availableJobs.find(j => j.id === jobId);
        if (!job || !this.canAcceptJob(job)) return null;
        job.status = 'in_progress';
        this.activeJobs.push(job);
        return job;
    }

    getActiveJob(jobId) {
        return this.activeJobs.find(j => j.id === jobId);
    }

    completeStep(job, success) {
        const step = job.scenario.steps[job.stepIndex];
        const energyCost = Math.round(STEP_ENERGY_COST * this.energyModifier);
        this.energy = clamp(this.energy - energyCost, 0, this.maxEnergy);
        if (success) {
            job.stepIndex += 1;
            if (job.stepIndex >= job.scenario.steps.length) {
                job.status = 'completed';
                this.coins += job.reward;
                this.reputation = clamp(this.reputation + job.reputationGain, 0, 10);
                this.completedToday += 1;
            }
        } else {
            job.status = 'failed';
            this.reputation = clamp(this.reputation - 0.4, 0, 10);
        }
    }

    getCompletedJobs() {
        return this.activeJobs.filter(job => job.status === 'completed');
    }

    purchaseUpgrade(upgradeId) {
        const upgrade = UPGRADES.find(u => u.id === upgradeId);
        if (!upgrade || this.purchasedUpgrades.has(upgradeId)) return false;
        if (this.coins < upgrade.cost) return false;
        this.coins -= upgrade.cost;
        this.purchasedUpgrades.add(upgradeId);
        upgrade.effect(this);
        return true;
    }

    toSnapshot() {
        return {
            playerId: this.playerId,
            playerName: this.playerName,
            day: this.day,
            coins: this.coins,
            reputation: this.reputation,
            completed: this.getCompletedJobs().length,
            updatedAt: Date.now()
        };
    }
}

class LeaderboardSync {
    constructor() {
        this.channel = null;
        this.listeners = new Set();
        if ('BroadcastChannel' in window) {
            this.channel = new BroadcastChannel('agd_repair_tycoon_leaderboard');
            this.channel.addEventListener('message', event => {
                if (event.data?.type === 'leaderboard-update') {
                    this.saveSnapshot(event.data.payload);
                    this.notify();
                }
            });
        }
    }

    subscribe(listener) {
        this.listeners.add(listener);
    }

    notify() {
        const entries = this.getEntries();
        this.listeners.forEach(listener => listener(entries));
    }

    saveSnapshot(snapshot) {
        const data = this.getRawData();
        data[snapshot.playerId] = snapshot;
        localStorage.setItem(LEADERBOARD_KEY, JSON.stringify(data));
    }

    getRawData() {
        try {
            const raw = localStorage.getItem(LEADERBOARD_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (error) {
            console.warn('Błąd odczytu tablicy wyników', error);
            return {};
        }
    }

    getEntries() {
        const data = this.getRawData();
        const entries = Object.values(data);
        const threshold = Date.now() - 1000 * 60 * 60 * 12; // 12 godzin
        const filtered = entries.filter(entry => entry.updatedAt >= threshold);
        filtered.sort((a, b) => b.coins - a.coins);
        return filtered.slice(0, 8);
    }

    broadcast(snapshot) {
        this.saveSnapshot(snapshot);
        if (this.channel) {
            this.channel.postMessage({ type: 'leaderboard-update', payload: snapshot });
        }
        this.notify();
    }
}

class GameUI {
    constructor(state, leaderboard) {
        this.state = state;
        this.leaderboard = leaderboard;
        this.summaryNode = document.getElementById('player-summary');
        this.jobsNode = document.getElementById('jobs');
        this.activeJobsNode = document.getElementById('active-jobs-list');
        this.logNode = document.getElementById('event-log');
        this.upgradesNode = document.getElementById('upgrades-list');
        this.leaderboardNode = document.getElementById('leaderboard-list');
        this.jobTemplate = document.getElementById('job-card-template');
        this.upgradeTemplate = document.getElementById('upgrade-card-template');
        this.modal = document.getElementById('job-modal');
        this.modalTitle = document.getElementById('job-modal-title');
        this.modalBody = document.getElementById('job-modal-body');
        this.modalAction = document.getElementById('job-modal-action');
        this.modalClose = document.getElementById('job-modal-close');
        this.activeModalJob = null;
        this.modalClose.addEventListener('click', () => this.closeModal());
        this.modalAction.addEventListener('click', () => this.handleModalAction());
    }

    render() {
        this.renderSummary();
        this.renderJobs();
        this.renderActiveJobs();
        this.renderUpgrades();
        this.renderLeaderboard();
    }

    renderSummary() {
        const { day, coins, reputation, energy, maxEnergy, playerName } = this.state;
        this.summaryNode.innerHTML = `
            <div class="summary-pill">Dzień <strong>${day}</strong></div>
            <div class="summary-pill">Kapitał <strong>${formatCurrency(coins)}</strong></div>
            <div class="summary-pill">Reputacja <strong>${reputation.toFixed(1)}</strong></div>
            <div class="summary-pill">Energia <strong>${energy}/${maxEnergy}</strong></div>
            <div class="summary-pill">Serwis: <strong>${playerName}</strong></div>
        `;
    }

    renderJobs() {
        this.jobsNode.innerHTML = '';
        if (!this.state.availableJobs.length) {
            this.jobsNode.innerHTML = '<p class="placeholder">Brak nowych zleceń – rozpocznij dzień pracy.</p>';
            return;
        }
        const fragment = document.createDocumentFragment();
        for (const job of this.state.availableJobs) {
            const node = this.jobTemplate.content.firstElementChild.cloneNode(true);
            node.querySelector('.job-title').textContent = `${job.appliance} – ${job.scenario.issue}`;
            node.querySelector('.job-meta').textContent = `Klient: ${job.client} • Wynagrodzenie: ${formatCurrency(job.reward)}`;
            node.querySelector('.job-description').textContent = `Trudność: ${job.scenario.difficulty}/3 • Etapy: ${job.scenario.steps.length}`;
            const action = node.querySelector('.job-action');
            action.textContent = this.state.canAcceptJob(job) ? 'Przyjmij zlecenie' : 'Brak zasobów';
            action.disabled = !this.state.canAcceptJob(job);
            action.addEventListener('click', () => {
                const accepted = this.state.acceptJob(job.id);
                if (accepted) {
                    this.log(`Przyjęto zlecenie: ${job.appliance.toUpperCase()} dla ${job.client}`, 'success');
                    this.render();
                }
            });
            fragment.appendChild(node);
        }
        this.jobsNode.appendChild(fragment);
    }

    renderActiveJobs() {
        this.activeJobsNode.innerHTML = '';
        if (!this.state.activeJobs.length) {
            this.activeJobsNode.innerHTML = '<p class="placeholder">Brak aktywnych zleceń.</p>';
            return;
        }
        for (const job of this.state.activeJobs) {
            const card = this.jobTemplate.content.firstElementChild.cloneNode(true);
            card.querySelector('.job-title').textContent = `${job.appliance} – ${job.scenario.issue}`;
            const remaining = job.scenario.steps.length - job.stepIndex;
            card.querySelector('.job-meta').textContent = `Klient: ${job.client} • Postęp: ${job.stepIndex}/${job.scenario.steps.length}`;
            card.querySelector('.job-description').textContent = job.status === 'completed'
                ? `Ukończono! Wynagrodzenie: ${formatCurrency(job.reward)}`
                : job.status === 'failed'
                    ? 'Klient niezadowolony – reputacja spadła.'
                    : `Pozostało etapów: ${remaining}`;
            const action = card.querySelector('.job-action');
            if (job.status === 'in_progress') {
                action.textContent = 'Rozpocznij etap';
                action.disabled = this.state.energy < STEP_ENERGY_COST;
                action.addEventListener('click', () => this.openJobModal(job.id));
            } else {
                action.textContent = job.status === 'completed' ? 'Zamknij zlecenie' : 'Usuń z warsztatu';
                action.addEventListener('click', () => {
                    this.state.activeJobs = this.state.activeJobs.filter(j => j.id !== job.id);
                    this.log('Zamknięto zlecenie i przygotowano stanowisko dla kolejnego klienta.', 'success');
                    this.render();
                });
            }
            this.activeJobsNode.appendChild(card);
        }
    }

    renderUpgrades() {
        this.upgradesNode.innerHTML = '';
        const fragment = document.createDocumentFragment();
        for (const upgrade of UPGRADES) {
            const node = this.upgradeTemplate.content.firstElementChild.cloneNode(true);
            node.querySelector('.upgrade-title').textContent = upgrade.name;
            node.querySelector('.upgrade-meta').textContent = `Cena: ${formatCurrency(upgrade.cost)}`;
            node.querySelector('.upgrade-description').textContent = upgrade.description;
            const button = node.querySelector('.upgrade-action');
            const owned = this.state.purchasedUpgrades.has(upgrade.id);
            button.textContent = owned ? 'Zakupiono' : 'Kup ulepszenie';
            button.disabled = owned || this.state.coins < upgrade.cost;
            button.addEventListener('click', () => {
                const success = this.state.purchaseUpgrade(upgrade.id);
                if (success) {
                    this.log(`Zainwestowano w: ${upgrade.name}`, 'success');
                    this.render();
                } else {
                    this.log('Brak środków lub ulepszenie już zakupione.', 'warning');
                }
            });
            fragment.appendChild(node);
        }
        this.upgradesNode.appendChild(fragment);
    }

    renderLeaderboard(entries = this.leaderboard.getEntries()) {
        this.leaderboardNode.innerHTML = '';
        if (!entries.length) {
            this.leaderboardNode.innerHTML = '<p class="placeholder">Brak danych – graj w wielu kartach, aby porównać wyniki.</p>';
            return;
        }
        const fragment = document.createDocumentFragment();
        for (const [index, entry] of entries.entries()) {
            const node = document.createElement('div');
            node.className = 'leaderboard-item';
            node.innerHTML = `
                <span>#${index + 1} <strong>${entry.playerName}</strong> (Dzień ${entry.day})</span>
                <span>${formatCurrency(entry.coins)}</span>
            `;
            fragment.appendChild(node);
        }
        this.leaderboardNode.appendChild(fragment);
    }

    openJobModal(jobId) {
        const job = this.state.getActiveJob(jobId);
        if (!job || job.status !== 'in_progress') return;
        this.activeModalJob = job;
        this.updateModalContent(job);
        this.modal.removeAttribute('hidden');
    }

    updateModalContent(job) {
        if (job.status === 'completed') {
            this.modalTitle.textContent = 'Zlecenie zakończone';
            this.modalBody.innerHTML = `
                <p>Perfekcyjnie ukończono naprawę! Wypłacono ${formatCurrency(job.reward)} oraz zdobyto ${job.reputationGain.toFixed(1)} reputacji.</p>
            `;
            this.modalAction.textContent = 'Zamknij';
            this.modalAction.disabled = false;
            return;
        }
        const step = job.scenario.steps[job.stepIndex];
        this.modalTitle.textContent = `${step.phase} – ${job.appliance}`;
        this.modalBody.innerHTML = `
            <p>${step.question}</p>
            <div class="quiz-options"></div>
        `;
        const optionsNode = this.modalBody.querySelector('.quiz-options');
        step.options.forEach((option, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = option;
            button.addEventListener('click', () => this.evaluateAnswer(job, step, index));
            optionsNode.appendChild(button);
        });
        this.modalAction.textContent = 'Anuluj';
        this.modalAction.disabled = false;
    }

    evaluateAnswer(job, step, selectedIndex) {
        const success = selectedIndex === step.answerIndex;
        this.state.completeStep(job, success);
        if (success) {
            this.log(step.successText, 'success');
        } else {
            this.log('Klient niezadowolony z błędnej diagnozy – reputacja spadła.', 'danger');
        }
        if (job.status === 'completed' || job.status === 'failed') {
            this.leaderboard.broadcast(this.state.toSnapshot());
        }
        this.render();
        if (job.status === 'in_progress') {
            this.updateModalContent(job);
        } else {
            this.updateModalContent(job);
        }
    }

    closeModal() {
        this.modal.setAttribute('hidden', '');
        this.activeModalJob = null;
    }

    handleModalAction() {
        if (!this.activeModalJob) {
            this.closeModal();
            return;
        }
        if (this.activeModalJob.status === 'in_progress') {
            this.closeModal();
        } else {
            this.closeModal();
            this.render();
        }
    }

    log(message, tone = 'success') {
        const entry = document.createElement('p');
        entry.className = `log-entry ${tone}`;
        entry.textContent = `[Dzień ${this.state.day}] ${message}`;
        this.logNode.prepend(entry);
        while (this.logNode.childNodes.length > 12) {
            this.logNode.removeChild(this.logNode.lastChild);
        }
    }
}

function requestPlayerName() {
    let saved = localStorage.getItem(`${PLAYER_KEY}_name`);
    if (saved) return saved;
    const name = prompt('Podaj nazwę swojego serwisu AGD:', 'Serwis Pro-AGD');
    if (name) {
        localStorage.setItem(`${PLAYER_KEY}_name`, name);
        return name;
    }
    return 'Serwis Pro-AGD';
}

function setupGame() {
    const playerName = requestPlayerName();
    const state = new GameState(playerName);
    const leaderboard = new LeaderboardSync();
    const ui = new GameUI(state, leaderboard);
    leaderboard.subscribe(entries => ui.renderLeaderboard(entries));

    const newDayBtn = document.getElementById('new-day-btn');
    newDayBtn.addEventListener('click', () => {
        const jobs = state.startDay();
        ui.log(`Rozpoczęto dzień ${state.day}. Wygenerowano ${jobs.length} zlecenia.`, 'success');
        ui.render();
        leaderboard.broadcast(state.toSnapshot());
    });

    ui.render();
    leaderboard.notify();
}

window.addEventListener('DOMContentLoaded', setupGame);
