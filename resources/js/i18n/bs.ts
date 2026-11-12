/**
 * All UI copy, Bosnian (ijekavica, Latin script). Strings that come from the
 * prototype are verbatim; do not reword them.
 */
export const bs = {
    appName: 'Rokovnik',

    landing: {
        metaTitle: 'Asistent za advokatsku kancelariju',
        metaDescription:
            'Fotografija sudskog pismena postaje prijedlog unosa u knjigu pošte, prijedlog roka i podsjetnik. Advokat sve pregleda, ispravlja i potvrđuje — tek tada rok postaje aktivan.',
        badge: 'Prototip · demo podaci',
        nav: {
            flow: 'Tok',
            oversight: 'Nadzor',
            pilot: 'Pilot',
            login: 'Prijava',
            openApp: 'Otvori aplikaciju',
            cta: 'Pogledaj prototip',
        },
        hero: {
            kicker: 'Asistent za advokatsku kancelariju',
            title: 'Od primljene pošte do potvrđenog roka. Bez prepisivanja.',
            lead: 'Fotografija sudskog pismena postaje prijedlog unosa u knjigu pošte, prijedlog roka i podsjetnik. Advokat sve pregleda, ispravlja i potvrđuje — tek tada rok postaje aktivan.',
            cta: 'Pogledaj prototip →',
            // Restored in Phase 8 together with the live demo.
            ctaNote: 'Telefon desno je već aktivan — možete kliknuti odmah.',
            tableCase: 'Predmet 65 0 P 084512 25 P',
            tableFictional: 'izmišljen',
            rows: [
                {
                    time: '08:12',
                    text: 'Fotografisano rješenje suda',
                    tag: 'Izvor',
                    tone: 'recorded',
                },
                {
                    time: '08:13',
                    text: 'Prijedlog roka: 26.11.2026.',
                    tag: 'Predloženo',
                    tone: 'proposed',
                },
                {
                    time: '08:15',
                    text: 'Rok upisan u rokovnik, podsjetnici postavljeni',
                    tag: 'Potvrdio advokat',
                    tone: 'confirmed',
                },
            ],
            disclaimer:
                'Svi podaci u prototipu su izmišljeni. Sistem nije u upotrebi.',
        },
        flow: {
            kicker: 'Tok na jednom izmišljenom predmetu',
            title: 'Jedan dokument postaje radnja kojoj se može ući u trag.',
            steps: [
                {
                    meta: '01 · 11.11. 08:12',
                    title: 'Dokument',
                    text: 'Rješenje suda od 06.11.2026. fotografisano telefonom.',
                    chips: [{ label: 'Izvor', status: 'recorded' }],
                },
                {
                    meta: '02 · 11.11. 08:13',
                    title: 'Knjiga pošte',
                    text: 'Unos U-2026/041: sud, broj predmeta, vrsta, datum prijema. Advokat pregleda i ispravi polja.',
                    chips: [{ label: 'Potvrdio advokat', status: 'confirmed' }],
                },
                {
                    meta: '03 · 11.11. 08:15',
                    title: 'Rok',
                    quote: '„…u roku od 15 dana od dana prijema…“',
                    text: 'Prijedlog: 26.11.2026., uz izvor i pretpostavke.',
                    chips: [
                        { label: 'Predloženo', status: 'proposed' },
                        { label: '→ Potvrdio advokat', status: 'confirmed' },
                    ],
                },
                {
                    meta: '04 · 23.11. i 25.11.',
                    title: 'Podsjetnik',
                    text: 'Rok se javlja u jutarnjem pregledu tri dana i dan prije isteka.',
                    chips: [{ label: 'Postavio advokat', status: 'confirmed' }],
                },
                {
                    meta: '05 · 11.11. 11:52',
                    title: 'Radnja',
                    text: 'Ročište zabilježeno glasom, podnesak predat na pisarnici, s dokazom o predaji.',
                    chips: [{ label: 'Evidentirano', status: 'recorded' }],
                },
                {
                    meta: '06 · u predmetu',
                    title: 'Troškovnik',
                    text: 'Evidentirane radnje čekaju kao stavke nacrta. Advokat mijenja i odobrava svaku.',
                    chips: [{ label: 'Nacrt za pregled', status: 'proposed' }],
                },
            ],
        },
        benefits: {
            title: 'Šta mala kancelarija dobija',
            items: [
                {
                    title: 'Manje ručnog unosa',
                    text: 'Podatak se unosi jednom, s dokumenta, i koristi se u knjizi pošte, rokovniku i troškovniku.',
                },
                {
                    title: 'Rokovi na vidiku',
                    text: 'Jutarnji pregled pokazuje današnja ročišta i rokove koji ističu u naredna tri dana.',
                },
                {
                    title: 'Trag svake radnje',
                    text: 'Svaki unos vodi nazad na izvorni dokument, a značajne izmjene ostaju u dnevniku.',
                },
                {
                    title: 'Lakša priprema troškova',
                    text: 'Radnje se bilježe kad se dogode, pa se troškovnik ne rekonstruiše na kraju postupka.',
                },
            ],
        },
        oversight: {
            kicker: 'Povjerenje i nadzor',
            title: 'Pitanja koja ćete prvo postaviti — i šta još nije riješeno.',
            lead: 'Desna kolona nisu ugrađene sigurnosne funkcije. To su uslovi koje treba riješiti prije rada sa stvarnim predmetima.',
            inPrototype: 'U prototipu:',
            pilotCondition: 'Uslov za pilot:',
            items: [
                {
                    question: 'Gdje bi se podaci čuvali?',
                    prototype:
                        'nigdje. Demo podaci postoje samo u ovom pregledniku.',
                    pilot: 'odabrati mjesto i pružaoca pohrane zajedno s kancelarijom.',
                },
                {
                    question: 'Ko bi imao pristup?',
                    prototype: 'samo osoba koja ga gleda.',
                    pilot: 'pisano utvrditi ko, osim advokata, tehnički može doći do podataka i pod kojim uslovima.',
                },
                {
                    question:
                        'Mogu li se dokumenti stranaka koristiti za treniranje modela?',
                    prototype: 'ne obrađuju se stvarni dokumenti.',
                    pilot: 'ugovorom s pružaocem modela isključiti takvu upotrebu. Bez toga pilot ne počinje.',
                },
                {
                    question: 'Kako se bilježe izmjene?',
                    prototype:
                        'dnevnik u predmetu pokazuje ko je i kada potvrdio ili izmijenio rok, unos ili stavku.',
                    pilot: 'odrediti koliko se dnevnik čuva i ko ga može pregledati.',
                },
                {
                    question: 'Kako se podaci mogu izvesti?',
                    prototype: 'dugme za izvoz je prikazano, ali ne radi.',
                    pilot: 'dogovoriti formate izvoza i potpuno preuzimanje podataka po završetku.',
                },
            ],
            closing:
                'Svaki prijedlog aplikacije ostaje prijedlog. Rok, unos i stavka troškovnika vrijede tek kada ih advokat potvrdi.',
        },
        pilot: {
            kicker: 'Prijedlog',
            title: 'Jedan mjesec, jedan advokat, četiri funkcije.',
            lead: 'Rokovnik, knjiga pošte, podsjetnici i nacrt troškovnika. Postojeći rokovnik se vodi paralelno cijelo vrijeme.',
            weeks: [
                {
                    when: 'Prije početka',
                    text: 'Riješiti uslove iz prethodnog odjeljka: pohrana, pristup, treniranje modela, izvoz.',
                },
                {
                    when: '1. sedmica',
                    text: 'Unos tekućih predmeta i zatečenih rokova.',
                },
                {
                    when: '2. i 3. sedmica',
                    text: 'Svakodnevni rad: fotografisanje pošte, potvrđivanje rokova, bilješke s ročišta.',
                },
                {
                    when: '4. sedmica',
                    text: 'Pregled: gdje je prijedlog bio pogrešan, šta je smetalo, šta je uštedjelo vrijeme.',
                },
            ],
        },
        later: {
            title: 'Kasnije, ne sada',
            text: 'Obračun sudskih taksi · fakture i uplate · obavijesti strankama · rokovi zastare · provjera sukoba interesa · raspodjela zadataka u timu · predlošci dokumenata prilagođeni stilu pisanja. Ništa od ovoga nije dio prototipa.',
        },
        footer: {
            text: '„Rokovnik“ je radni naziv. Ovo je prototip, a ne proizvod u upotrebi; advokatska komora ga nije odobrila niti podržala. Svi predmeti, stranke, dokumenti i iznosi su izmišljeni.',
            cta: 'Pogledaj prototip →',
        },
        phoneLabel: 'Prikaz ekrana „Danas“ u aplikaciji',
    },

    today: {
        title: 'Danas',
        briefing: 'Jutarnji pregled',
        groups: {
            hearings: 'Ročišta danas',
            deadlines: 'Rokovi u naredna tri dana',
            court: 'Provjeriti u sudu',
            review: 'Čeka vaš pregled',
            tomorrow: 'Sutra',
        },
        noDeadlines: 'Nema rokova u naredna tri dana.',
        quickInput: 'Brzi unos',
        capture: 'Fotografiši',
        dictate: 'Diktiraj',
        submission: 'Podnesak',
    },

    matters: {
        title: 'Predmeti',
        next: 'Naredno:',
        costs: 'Troškovnik:',
        demoNote: 'U prototipu je prikazan jedan izmišljen predmet.',
        back: '‹ Predmeti',
        costButton: 'Troškovnik',
        tabs: {
            timeline: 'Hronologija',
            costs: 'Troškovnik',
            audit: 'Izmjene',
        },
        upcoming: 'Predstoji',
        recorded: 'Evidentirano',
        auditIntro: 'Dnevnik značajnih izmjena u predmetu.',
        empty: 'Nema unosa.',
    },

    document: {
        fictional: 'DEMO — izmišljen dokument',
        openFile: 'Otvori originalnu datoteku ›',
    },

    status: {
        proposed: 'Predloženo · čeka pregled',
        confirmed: 'Potvrdio advokat',
        urgent: 'Potvrdio advokat · ističe uskoro',
        recorded: 'Evidentirano',
        fulfilled: 'Ispunjeno',
    },

    tabs: {
        today: 'Danas',
        matters: 'Predmeti',
        calendar: 'Kalendar',
        mail: 'Pošta',
    },

    common: {
        back: 'Nazad',
        close: 'Zatvori',
        cancel: 'Odustani',
        save: 'Sačuvaj',
        sourceDocument: 'Izvorni dokument ›',
        open: 'Otvori ›',
        currency: 'KM',
        dateHint: 'Unesite datum u obliku dd.mm.gggg.',
        removeItem: 'Ukloni stavku',
        mainNav: 'Glavna navigacija',
    },

    auth: {
        loginTitle: 'Prijava',
        loginDescription: 'Unesite e-mail i lozinku kancelarije.',
        email: 'E-mail',
        password: 'Lozinka',
        remember: 'Zapamti me na ovom uređaju',
        submit: 'Prijavi se',
        noAccount:
            'Račune otvara administrator kancelarije. Javna registracija nije moguća.',
        backHome: '‹ Rokovnik',
    },

    settings: {
        title: 'Postavke',
        profile: 'Podaci o korisniku',
        name: 'Ime i prezime',
        email: 'E-mail',
        saveProfile: 'Sačuvaj podatke',
        password: 'Promjena lozinke',
        currentPassword: 'Trenutna lozinka',
        newPassword: 'Nova lozinka',
        confirmPassword: 'Ponovite novu lozinku',
        savePassword: 'Promijeni lozinku',
        logout: 'Odjava',
        open: 'Postavke',
    },
} as const;

export type Bs = typeof bs;
