-- Atualiza o usuário para admin
UPDATE usuarios SET tipo = 'admin' WHERE email = 'SEU_EMAIL_AQUI';

-- Verifica se a atualização foi bem sucedida
SELECT id, nome, email, tipo FROM usuarios WHERE tipo = 'admin';
