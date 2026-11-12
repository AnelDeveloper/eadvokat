import { useState } from 'react';
import type { ViewStyle } from 'react-native';
import { formatDate, parseDate } from '@shared/lib/format';
import { TextField } from '@/components/ui/TextField';

/** ISO date (YYYY-MM-DD) in, typed as dd.mm.gggg.; emits null while invalid. */
export function DateField({
    value,
    onChange,
    label,
    note,
    error,
    warn,
    mono = true,
    large = true,
    style,
}: {
    value: string | null;
    onChange: (iso: string | null) => void;
    label?: string;
    note?: string;
    error?: string;
    warn?: boolean;
    mono?: boolean;
    large?: boolean;
    style?: ViewStyle;
}) {
    const [text, setText] = useState(formatDate(value));
    const [shown, setShown] = useState(value);

    // The parent changed the date (e.g. a recomputed last day): show it.
    if (value !== shown) {
        setShown(value);

        if (value !== parseDate(text)) {
            setText(formatDate(value));
        }
    }

    function commit(): void {
        const iso = parseDate(text);
        onChange(iso);

        if (iso) {
            setText(formatDate(iso));
        }
    }

    return (
        <TextField
            label={label}
            note={note}
            error={error}
            warn={warn}
            mono={mono}
            fontSize={large ? 16 : 15}
            value={text}
            onChangeText={setText}
            onEndEditing={commit}
            onBlur={commit}
            placeholder="dd.mm.gggg."
            keyboardType="numbers-and-punctuation"
            autoCorrect={false}
            style={style}
        />
    );
}
