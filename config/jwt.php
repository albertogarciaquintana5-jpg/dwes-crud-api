<?php
// Configuración JWT sencilla que lee las variables de entorno o usa valores por defecto
return [
    'secret' => getenv('JWT_SECRET') !== false ? getenv('JWT_SECRET') : 'test_secret_for_teacher',
    'algo' => getenv('JWT_ALGO') !== false ? getenv('JWT_ALGO') : 'HS256',
    'exp_seconds' => (int)(getenv('JWT_EXP_SECONDS') !== false ? getenv('JWT_EXP_SECONDS') : 3600),
];
