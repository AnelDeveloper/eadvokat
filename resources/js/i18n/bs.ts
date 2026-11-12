/**
 * All UI copy, Bosnian (ijekavica, Latin script). Strings that come from the
 * prototype are verbatim; do not reword them.
 */
export const bs = {
    appName: 'Rokovnik',

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
