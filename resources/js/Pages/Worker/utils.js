export const formatTimestamp = (timestamp) => {
    if (!timestamp || isNaN(new Date(timestamp).getTime())) {
        return '-';
    }
    return new Date(timestamp).toLocaleString();
};

export const formatDate = (timestamp) => {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleDateString();
};

export const formatDuration = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return [hours, minutes, secs]
        .map((val) => String(val).padStart(2, '0'))
        .join(':');
};


export const formatTime = (timestamp) => {
    if (!timestamp || isNaN(new Date(timestamp).getTime())) {
        return '-';
    }

    const date = new Date(timestamp);
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');

    return `${hours}:${minutes}`;
};


export const generateMapLink = (longitude, latitude) => {
    if (!longitude || !latitude) {
        return '-'; // Возвращаем дефолтное значение, если координаты отсутствуют
    }
    return `https://yandex.ru/maps/?pt=${longitude},${latitude}&z=18&l=map`;
};
