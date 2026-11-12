import * as DocumentPicker from 'expo-document-picker';
import * as ImagePicker from 'expo-image-picker';

export type PickedFile = { uri: string; name: string; type: string };

/** Camera first; returns null if cancelled. Throws 'denied' without permission. */
export async function takePhoto(): Promise<PickedFile | null> {
    const permission = await ImagePicker.requestCameraPermissionsAsync();

    if (!permission.granted) {
        throw new Error('denied');
    }

    const result = await ImagePicker.launchCameraAsync({ mediaTypes: ['images'], quality: 0.8 });

    return fromImage(result);
}

export async function pickPhoto(): Promise<PickedFile | null> {
    const result = await ImagePicker.launchImageLibraryAsync({ mediaTypes: ['images'], quality: 0.8 });

    return fromImage(result);
}

/** A PDF or an image from Files / Drive. */
export async function pickDocument(): Promise<PickedFile | null> {
    const result = await DocumentPicker.getDocumentAsync({ type: ['application/pdf', 'image/*'], copyToCacheDirectory: true });

    if (result.canceled || !result.assets[0]) {
        return null;
    }

    const asset = result.assets[0];

    return { uri: asset.uri, name: asset.name, type: asset.mimeType ?? 'application/pdf' };
}

function fromImage(result: ImagePicker.ImagePickerResult): PickedFile | null {
    if (result.canceled || !result.assets[0]) {
        return null;
    }

    const asset = result.assets[0];

    return { uri: asset.uri, name: asset.fileName ?? 'dokument.jpg', type: asset.mimeType ?? 'image/jpeg' };
}
