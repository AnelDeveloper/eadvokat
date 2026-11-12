/**
 * Design tokens from design_handoff_rokovnik/README.md — the same values as
 * resources/css/app.css on the web. The only place colours are written down.
 */
export const colors = {
    ink: '#173247',
    inkDeep: '#0F2230',
    body: '#2C4A5E',
    muted: '#4F6676',
    faint: '#8FA1AD',
    lineStrong: '#C3CED5',
    line: '#D5DDE2',
    hairline: '#E6ECEF',
    app: '#F3F6F7',
    neutralBg: '#EDF1F3',
    viewer: '#DDE4E8',
    surface: '#FFFFFF',
    white: '#FFFFFF',
    confirmed: '#3F776F',
    confirmedText: '#2F5F58',
    confirmedBg: '#E4EFEC',
    proposed: '#B27B34',
    proposedText: '#7A4F17',
    proposedBg: '#F7EEDF',
    urgent: '#A34A45',
    urgentText: '#8A3B37',
    urgentBg: '#F6E9E8',
    scrim: 'rgba(15,34,48,0.5)',
    overlay: 'rgba(15,34,48,0.78)',
    transparent: 'transparent',
} as const;

export const radius = {
    chip: 4,
    checkbox: 5,
    field: 8,
    card: 10,
    sheet: 18,
    pill: 999,
} as const;

/** Heights: nothing tappable under 44. */
export const size = {
    tap: 44,
    field: 44,
    primary: 52,
    quick: 56,
    row: 56,
    tabBar: 68,
} as const;

export const fonts = {
    sans400: 'IBMPlexSans_400Regular',
    sans500: 'IBMPlexSans_500Medium',
    sans600: 'IBMPlexSans_600SemiBold',
    mono400: 'IBMPlexMono_400Regular',
    mono500: 'IBMPlexMono_500Medium',
    serif400: 'Newsreader_400Regular',
    serif400Italic: 'Newsreader_400Regular_Italic',
    serif500: 'Newsreader_500Medium',
    serif600: 'Newsreader_600SemiBold',
} as const;

export const shadow = {
    toast: { shadowColor: '#0F2230', shadowOpacity: 0.35, shadowRadius: 10, shadowOffset: { width: 0, height: 8 }, elevation: 6 },
    page: { shadowColor: '#173247', shadowOpacity: 0.2, shadowRadius: 3, shadowOffset: { width: 0, height: 1 }, elevation: 2 },
} as const;
