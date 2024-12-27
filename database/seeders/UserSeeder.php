<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Оспанова Магрипа Мырзалиевна',
                'role' => 'worker',
                'position' => 'Директор',
                'email' => 'magripa.ospanova14@mail.ru',
                'password' => Hash::make('F7g$9LpX'), // Пароль: F7g$9LpX
            ],
            [
                'name' => 'Абенов Жумабек Куантаевич',
                'role' => 'worker',
                'position' => 'Бас дәрігер',
                'email' => 'abenov.010@mail.ru',
                'password' => Hash::make('Qm5@8Vtr'), // Пароль: Qm5@8Vtr
            ],
            [
                'name' => 'Байдуллаев Тимур Батырович',
                'role' => 'worker',
                'position' => 'Бас дәр-дің емдеу ісі б-ша орынб.',
                'email' => 'timavscancer@mail.ru',
                'password' => Hash::make('Nt6*4Kpo'), // Пароль: Nt6*4Kpo
            ],
            [
                'name' => 'Төленды Жандос Болатұлы',
                'role' => 'worker',
                'position' => 'Бас дәр-дің ұйымд-ру ісі б-ша орынб.',
                'email' => 'Tolendy.Zhandos@mail.ru',
                'password' => Hash::make('Xp8&5Lto'), // Пароль: Xp8&5Lto
            ],
            [
                'name' => 'Лысенко Елена Владимировна',
                'role' => 'worker',
                'position' => 'Хирург',
                'email' => 'Katheelena@mail.ru',
                'password' => Hash::make('Kq3@7Rmp'), // Пароль: Kq3@7Rmp
            ],
            [
                'name' => 'Аширова Жанат Умирзаққызы',
                'role' => 'worker',
                'position' => 'Эпидемиолог',
                'email' => 'Zhan_zhanka@bk.ru',
                'password' => Hash::make('Tr5$9Vlk'), // Пароль: Tr5$9Vlk
            ],
            [
                'name' => 'Сатымбекова Барно Улугбековна',
                'role' => 'worker',
                'position' => 'Врач-узист',
                'email' => 'barno.satymbekova.1985@mail.ru',
                'password' => Hash::make('Pf8#6Xmn'), // Пароль: Pf8#6Xmn
            ],
            [
                'name' => 'Таджимуратова Айгерим Рысхановна',
                'role' => 'worker',
                'position' => 'Врач-гинеколог',
                'email' => 'Aykonia_94@mail.ru',
                'password' => Hash::make('Rq9*7Plm'), // Пароль: Rq9*7Plm
            ],
            [
                'name' => 'Жұманова Аида Тәңірбергенқызы',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'Muslima565@mail.ru',
                'password' => Hash::make('Vm2&8Qlk'), // Пароль: Vm2&8Qlk
            ],
            [
                'name' => 'Айнабекова Динара Каримжановна',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'dinara.ainabekova88@gmail.com',
                'password' => Hash::make('Bp4@5Lmv'), // Пароль: Bp4@5Lmv
            ],
            [
                'name' => 'Көшекова Гүлзат Мақсатқызы',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'Gulzat_40_99@mail.ru',
                'password' => Hash::make('Nl7$6Tqp'), // Пароль: Nl7$6Tqp
            ],
            [
                'name' => 'Орынбасар Саламат Жанболатқызы',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'Salamat1996oszh@mail.ru',
                'password' => Hash::make('Tr6#4Lkp'), // Пароль: Tr6#4Lkp
            ],
            [
                'name' => 'Бүркітбаев Қазыбек Асылханұлы',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'kazybekburkitbai@mail.ru',
                'password' => Hash::make('Lp8@6Qrk'), // Пароль: Lp8@6Qrk
            ],
            [
                'name' => 'Сейітқұлов Нұрислам Есенәліұлы',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'Seitkulov4518@gmail.com',
                'password' => Hash::make('Xp9$3Tmn'), // Пароль: Xp9$3Tmn
            ],
            [
                'name' => 'Қожатаева Аружан Ерланқызы',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'kojatayeva@gmail.com',
                'password' => Hash::make('Kq5&7Plk'), // Пароль: Kq5&7Plk
            ],
            [
                'name' => 'Болысбеков Бекежан Батырханович',
                'role' => 'worker',
                'position' => 'ЖТД',
                'email' => 'Doctor.723@mail.ru',
                'password' => Hash::make('Tr8@6Xmp'), // Пароль: Tr8@6Xmp
            ],
            [
                'name' => 'Оразбаева Сауле Калмурзаевна',
                'role' => 'worker',
                'position' => 'Старшая медсестра',
                'email' => 'Orazbayeva.1989@mail.ru',
                'password' => Hash::make('Vm2*9Rlk'), // Пароль: Vm2*9Rlk
            ],
            [
                'name' => 'Аманжолова Сания Нуртасқызы',
                'role' => 'worker',
                'position' => 'Аймақтық медбике',
                'email' => 'amanzholova.sania@interten.ru',
                'password' => Hash::make('Nt7&3Pqo'), // Пароль: Nt7&3Pqo
            ],
            [
                'name' => 'Мақұлбек Назерке Қайратқызы',
                'role' => 'worker',
                'position' => 'Аймақтық медбике',
                'email' => 'nazerkemakulbek6@gmail.com',
                'password' => Hash::make('Vl7*3Pko'), // Пароль: Vl7*3Pko
            ],
            [
                'name' => 'Дауылбай Орынбасар Полатбекұлы',
                'role' => 'worker',
                'position' => 'Аймақтық медаға',
                'email' => 'erka5692@gmail.com',
                'password' => Hash::make('Lp9$5Xmt'), // Пароль: Lp9$5Xmt
            ],
            [
                'name' => 'Сералы Зәуре Сералықызы',
                'role' => 'worker',
                'position' => 'Аймақтық медбике',
                'email' => 'Zaure.seraly96@gmail.com',
                'password' => Hash::make('Xm3&9Tqp'), // Пароль: Xm3&9Tqp
            ],
            [
                'name' => 'Әділова Назерке Ғалымқызы',
                'role' => 'worker',
                'position' => 'Аймақтық медбике',
                'email' => 'Nazerke.adilova99@mail.ru',
                'password' => Hash::make('Kt6@3Rlm'), // Пароль: Kt6@3Rlm
            ],
            [
                'name' => 'Имамова Акмаржан Султанқызы',
                'role' => 'worker',
                'position' => 'Аймақтық медбике',
                'email' => 'akmarzhan.imamova@mail.ru',
                'password' => Hash::make('Nt8$2Vlm'), // Пароль: Nt8$2Vlm
            ],
            [
                'name' => 'Турсынқұл Мағжан Саматұлы',
                'role' => 'worker',
                'position' => 'Аймақтық медаға',
                'email' => 'Tursunkul_Magzhan98@mail.ru',
                'password' => Hash::make('Qt4@8Vmn'), // Пароль: Qt4@8Vmn
            ],
            [
                'name' => 'Жолдыбек Мөлдір Бахытжанқызы',
                'role' => 'worker',
                'position' => 'Аймақтық медбике',
                'email' => 'mldr.zholdybek@mail.ru',
                'password' => Hash::make('Kp7&3Tlv'), // Пароль: Kp7&3Tlv
            ],
            [
                'name' => 'Кенжебеков Арсен Рустемұлы',
                'role' => 'worker',
                'position' => 'Аймақтық медаға',
                'email' => 'arsen_kenzhebekov@mail.ru',
                'password' => Hash::make('Lp8@6Qrk'), // Пароль: Lp8@6Qrk
            ],
            [
                'name' => 'Ергешова Динара Ғафуржанқызы',
                'role' => 'worker',
                'position' => 'Аймақтық медбике',
                'email' => 'dinara.yergeshova.05@mail.ru',
                'password' => Hash::make('Xp2&9Lkm'), // Пароль: Xp2&9Lkm
            ],
            [
                'name' => 'Тәжібай Ұлдана Ғанибекқызы',
                'role' => 'worker',
                'position' => 'Психолог',
                'email' => 'tazhibay.uldana@mail.ru',
                'password' => Hash::make('Tr5$9Vlk'), // Пароль: Tr5$9Vlk
            ],
        ];

        DB::table('users')->insert($users);
    }
}
