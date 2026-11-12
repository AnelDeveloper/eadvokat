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
                        'na serveru prototipa — samo izmišljeni demo podaci, odvojeno za svakog posjetioca i najviše 24 sata.',
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
                    prototype:
                        'nacrt troškovnika izvozi se u PDF; potpun izvoz svih podataka još ne postoji.',
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
        phoneLabel: 'Otvori prototip: ekran „Danas“ u aplikaciji',
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
        settings: 'Postavke',
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

    mail: {
        title: 'Knjiga pošte',
        incoming: 'Ulazna',
        outgoing: 'Izlazna',
        captureIncoming: 'Fotografiši primljeni dokument',
        recordOutgoing: 'Evidentiraj izlazni podnesak',
        empty: 'Nema unosa.',
    },

    capture: {
        cancel: 'Odustani',
        heading: 'Primljeni dokument',
        hint: 'Poravnajte dokument unutar okvira. U prototipu se koristi pripremljeni izmišljeni dokument.',
        hintReal: 'Poravnajte dokument unutar okvira ili odaberite PDF.',
        shutter: 'Fotografiši',
        reading: 'Čitam dokument…',
        fictional: 'DEMO — izmišljen dokument',
    },

    review: {
        title: 'Pregled unosa',
        step: '1/2',
        source: 'Izvorni dokument',
        sourceHint: 'Označeni dijelovi su izvor za polja ispod.',
        heading: 'Unos u knjigu pošte',
        incoming: 'ulazna pošta',
        court: 'Sud',
        caseNumber: 'Broj predmeta',
        docType: 'Vrsta dokumenta',
        documentDate: 'Datum dokumenta',
        receivedOn: 'Datum prijema',
        receivedAssumed:
            'Pretpostavljen današnji datum — provjerite s dostavnicom.',
        quotes: 'Izdvojeni navodi iz dokumenta',
        save: 'Potvrdi i sačuvaj u knjigu pošte',
    },

    calendar: {
        prev: 'Prethodni mjesec',
        next: 'Naredni mjesec',
        weekdays: ['pon', 'uto', 'sri', 'čet', 'pet', 'sub', 'ned'],
        legend: {
            event: 'ročište, sastanak',
            deadline: 'potvrđen rok',
            proposed: 'prijedlog',
            reminder: 'podsjetnik',
        },
        empty: 'Nema unosa za ovaj dan.',
    },

    deadline: {
        title: 'Prijedlog roka',
        step: '2/2',
        banner: 'Predloženo — rok nije aktivan.',
        bannerText:
            'U rokovnik ulazi tek kada potvrdite pravilo, početak i posljednji dan.',
        confirmedBanner: 'Rok je aktivan.',
        confirmedBannerText:
            'Ponovna potvrda s novim datumom zamjenjuje ranije podsjetnike.',
        source: 'Izvor',
        rule: '1 · Pravilo',
        ruleAck: 'Pravilo i nadležnost su tačni',
        start: '2 · Početak (datum prijema)',
        startHint: 'Iz knjige pošte. Prvi dan roka je naredni dan.',
        startAck: 'Datum početka je tačan',
        due: '3 · Posljednji dan roka',
        dueAck: 'Posljednji dan je tačan',
        dueComputed: (weekday: string, days: number) =>
            `${weekday} · ${days}. dan od početka`,
        dueManual: (weekday: string, days: number, proposed: number) =>
            `${weekday} · izmijenjeno ručno (${days}. dan od početka; prijedlog je bio ${proposed}.)`,
        assumptions: 'Pretpostavke obračuna — provjerite',
        assumptionList: [
            'Dan prijema se ne računa.',
            'Nije provjereno pada li posljednji dan na praznik ili neradni dan.',
            'Aplikacija ne ocjenjuje vrstu roka ni posljedice propuštanja.',
        ],
        reminders: 'Podsjetnici',
        remind3: (date: string) => `3 dana prije · ${date}`,
        remind1: (date: string) => `1 dan prije · ${date}`,
        confirm: (date: string) => `Potvrdi rok ${date}`,
        needsChecks: (n: number) => `Označite sve tri provjere (${n}/3)`,
        needsChecksToast:
            'Rok se ne može potvrditi dok ne označite pravilo, početak i posljednji dan.',
        invalidDue: 'Posljednji dan roka nije ispravan datum.',
        later: 'Ne sada — ostavi kao prijedlog',
        laterToast: 'Prijedlog ostaje na čekanju. Rok nije aktivan.',
    },

    dictation: {
        title: 'Bilješka s ročišta',
        idle: 'Dodirnite i diktirajte',
        idleHint:
            'Recite šta se desilo na ročištu, novi termin i šta je sud naložio.',
        recording: (time: string) => `Snimam… ${time}`,
        recordingHint:
            'Dodirnite ponovo da završite. U prototipu se koristi pripremljen diktat.',
        recordingHintReal: 'Dodirnite ponovo da završite.',
        processing: 'Obrađujem snimak…',
        micLabel: 'Diktiraj',
        noMic: 'Mikrofon nije dostupan. Dozvolite pristup mikrofonu u pregledniku.',
        useFixture: 'Nastavi s pripremljenim diktatom',
        noHearing: 'Danas nema ročišta za koje se bilježi bilješka.',
        transcript: 'Transkript · izvor prijedloga',
        eventCard: 'Događaj u kalendaru',
        eventTitle: (title: string) =>
            `Ročište — ${title.charAt(0).toLowerCase()}${title.slice(1)}`,
        date: 'Datum',
        time: 'Vrijeme',
        roomUnclear: (options: string[]) =>
            `Sudnica nije jasna u snimku: ${options.join(' ili ')}? Odaberite.`,
        roomChecked: (room: string) => `Sudnica ${room} — provjerio advokat.`,
        roomFirst: (options: string[]) =>
            `Prvo provjerite sudnicu: ${options.join(' ili ')}?`,
        room: (room: string) => `Sudnica ${room}`,
        deadlineCard: 'Rok',
        deadlineSource: (highlight: string, start: string) =>
            `Izvor: „${highlight}“ · početak ${start} (dan ročišta, ne računa se)`,
        dueLabel: 'Posljednji dan roka',
        noteCard: 'Bilješka u predmetu',
        confirm: 'Potvrdi',
        confirmed: 'Potvrđeno ✓ — vrati na pregled',
        reviewDeadline: 'Pregledaj i potvrdi rok ›',
        costHint: (date: string) =>
            `U nacrt troškovnika dodaje se prijedlog stavke „Zastupanje na ročištu ${date}“`,
        save: (n: number) => `Sačuvaj · potvrđeno ${n} od 3`,
        saveHint: 'Nepotvrđeno ostaje kao prijedlog i nije aktivno.',
        savedHint:
            'Bilješka je sačuvana. Nepotvrđeni prijedlozi čekaju vaš pregled.',
    },

    submission: {
        title: 'Izlazni podnesak',
        matter: 'Predmet',
        docType: 'Vrsta podneska',
        docTypePlaceholder: 'npr. Podnesak — dostava adrese svjedoka',
        sentOn: 'Datum predaje',
        method: 'Način predaje',
        proof: 'Dokaz o predaji',
        proofEmpty: 'Fotografiši prijemni štambilj',
        proofEmptySub: 'Ili potvrdu o predaji pošiljke',
        proofAttached: 'Dokaz priložen',
        proofThumb: (date: string) => `PRIM. ${date}`,
        fulfils: (date: string) => `Ovim podneskom ispunjen je rok ${date}`,
        reminder: 'Podsjetnik za provjeru',
        weekendNote: (date: string, weekday: string) =>
            `Deset dana od ${date} pada u ${weekday}. Odaberite dan:`,
        reminderOn: (date: string) => `Podsjetnik se javlja ${date}`,
        save: 'Sačuvaj i postavi podsjetnik',
    },

    costs: {
        title: 'Nacrt troškovnika',
        chipDraft: 'Nacrt · čeka pregled',
        chipApproved: 'Nacrt odobrio advokat',
        demoNotice: 'Svi iznosi su',
        demoNoticeBold: 'demo podaci',
        demoNoticeEnd:
            '. Nisu preuzeti iz važeće tarife i nemaju pravnu snagu.',
        notice: 'Iznose unosi ili potvrđuje advokat. Aplikacija ne sadrži tarife i ne računa iznose.',
        jurisdiction: 'Nadležnost',
        tariff: 'Izvor i verzija tarife (unosi advokat)',
        tariffPlaceholder: 'Naziv tarife i broj službenog glasila',
        tariffNoteDemo:
            'Prototip ne sadrži važeće tarife. Promjena nadležnosti ne preračunava demo iznose.',
        tariffNote:
            'Aplikacija ne sadrži tarife. Promjena nadležnosti ne preračunava iznose.',
        suffixDemo: 'KM · demo',
        suffix: 'KM',
        confirm: 'Potvrdi',
        revert: 'Vrati',
        remove: 'Ukloni stavku',
        totalDemo: 'Ukupno (demo iznosi)',
        total: 'Ukupno',
        approve: 'Odobri nacrt',
        approved: 'Nacrt odobren ✓',
        confirmFirst: (pending: string) =>
            `Prvo potvrdite sve stavke (${pending})`,
        confirmAllToast:
            'Nacrt se može odobriti tek kada pregledate i potvrdite svaku stavku.',
        tariffToast: 'Unesite izvor i verziju tarife.',
        amountToast: 'Unesite iznos prije potvrde stavke.',
        export: 'Izvezi nacrt (PDF)',
        nothingSent:
            'Ništa se ne šalje sudu ni stranci. Nacrt služi samo za pregled advokata.',
        empty: 'Nacrt još nema stavki.',
    },

    demo: {
        title: 'Prototip',
        back: '‹ Predstavljanje',
        reset: 'Vrati demo na početak',
        pill: 'Demo podaci',
        guideKicker: 'Tok prezentacije · oko 90 sekundi',
        guideTitle: 'Jedan predmet, od pošte do troškovnika',
        steps: [
            [
                'Jutarnji pregled',
                'Ekran „Danas“: ročište u 11:00, rok 13.11., provjera u pisarnici.',
            ],
            [
                'Fotografišite dokument',
                'Dodirnite „Fotografiši“, zatim okidač.',
            ],
            [
                'Pregledajte unos',
                'Provjerite polja i sačuvajte u knjigu pošte.',
            ],
            [
                'Potvrdite rok',
                'Označite pravilo, početak i posljednji dan, pa „Potvrdi rok“.',
            ],
            [
                'Rok u kalendaru',
                'Otvorite „Kalendar“: 26.11. i podsjetnici 23. i 25.11.',
            ],
            [
                'Bilješka glasom',
                '„Danas“ → „Diktiraj“. Ispravite sudnicu 21 → 12, potvrdite i sačuvajte.',
            ],
            [
                'Podnesak i podsjetnik',
                '„Danas“ → „Podnesak“. Priložite dokaz i sačuvajte.',
            ],
            ['Troškovnik', 'U predmetu otvorite „Troškovnik“.'],
            [
                'Odobrite nacrt',
                'Potvrdite preostale stavke, zatim „Odobri nacrt“.',
            ],
        ] as const,
        guideNote:
            'Stanje je izmišljeno i čuva se najviše 24 sata. „Vrati demo na početak“ briše sve unose.',
        sheetTitle: 'Demo podaci',
        sheetText:
            'Predmet, stranke, dokumenti i iznosi su izmišljeni. Ništa se ne šalje sudu, stranci ni trećim licima. Unosi se brišu nakon 24 sata ili kada vratite demo na početak.',
        phoneLabel: 'Aplikacija Rokovnik s demo podacima',
        blocked:
            'Prijavljeni ste kao korisnik kancelarije. Demo se otvara samo bez prijave, da se vaši podaci ne bi miješali s demo podacima.',
        openApp: 'Otvori aplikaciju',
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
