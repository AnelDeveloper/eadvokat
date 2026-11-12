import { Directory, File, Paths } from 'expo-file-system';
import * as Sharing from 'expo-sharing';
import { Platform } from 'react-native';
import { API_URL, authHeaders } from '@/lib/api';

/**
 * Downloads the cost-sheet PDF with the user's token and opens the system
 * share sheet, so the lawyer can save or view it. Nothing is sent anywhere.
 */
export async function shareCostSheetPdf(matterId: number, fileName: string): Promise<void> {
    const url = `${API_URL}/api/v1/matters/${matterId}/costs/pdf`;

    if (Platform.OS === 'web') {
        const response = await fetch(url, { headers: authHeaders() });
        const blob = await response.blob();
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = fileName;
        link.click();

        return;
    }

    const directory = new Directory(Paths.cache, 'troskovnik');

    if (!directory.exists) {
        directory.create();
    }

    const target = new File(directory, fileName);
    const file = await File.downloadFileAsync(url, target, { headers: authHeaders(), idempotent: true });
    await Sharing.shareAsync(file.uri, { mimeType: 'application/pdf', UTI: 'com.adobe.pdf' });
}
