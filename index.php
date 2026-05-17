<?php include 'form.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета разработчика</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>📄 Регистрационная анкета</h1>
            <p>Заполните данные о себе — все поля проверяются на сервере с использованием регулярных выражений</p>
        </div>
        
        <div class="form-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    ❌ При обработке формы обнаружены ошибки. Пожалуйста, исправьте их.
                </div>
                
                <div class="global-errors">
                    <strong>Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul>
                        <?php foreach ($errors as $field => $error): ?>
                            <?php if ($field != 'db'): ?>
                                <li><strong><?php echo ucfirst($field); ?>:</strong> <?php echo safeOutput($error['message']); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="process_form.php">
                <!-- ФИО -->
                <div class="field-group <?php echo getErrorClass('fullname'); ?>">
                    <label for="fullname">👤 ФИО *</label>
                    <div class="input-wrapper">
                        <input type="text" id="fullname" name="fullname" 
                               value="<?php echo safeOutput($formData['fullname'] ?? ''); ?>" required>
                        <?php if (isset($errors['fullname'])): ?>
                            <div class="error-message">
                                <?php echo safeOutput(getErrorMessage('fullname')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Телефон -->
                <div class="field-group <?php echo getErrorClass('phone'); ?>">
                    <label for="phone">📞 Телефон</label>
                    <div class="input-wrapper">
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo safeOutput($formData['phone'] ?? ''); ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="error-message">
                                <?php echo safeOutput(getErrorMessage('phone')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="field-group <?php echo getErrorClass('email'); ?>">
                    <label for="email">✉️ E-mail *</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" 
                               value="<?php echo safeOutput($formData['email'] ?? ''); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-message">
                                <?php echo safeOutput(getErrorMessage('email')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Дата рождения -->
                <div class="field-group <?php echo getErrorClass('birthdate'); ?>">
                    <label for="birthdate">🎂 Дата рождения</label>
                    <div class="input-wrapper">
                        <input type="date" id="birthdate" name="birthdate" 
                               value="<?php echo safeOutput($formData['birthdate'] ?? ''); ?>">
                        <?php if (isset($errors['birthdate'])): ?>
                            <div class="error-message">
                                <?php echo safeOutput(getErrorMessage('birthdate')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Пол -->
                <div class="field-group <?php echo getErrorClass('gender'); ?>">
                    <label>⚥ Пол</label>
                    <div class="input-wrapper radio-group">
                        <label><input type="radio" name="gender" value="male" 
                            <?php echo isRadioChecked('male', $formData); ?>> Мужской</label>
                        <label><input type="radio" name="gender" value="female" 
                            <?php echo isRadioChecked('female', $formData); ?>> Женский</label>
                        <label><input type="radio" name="gender" value="other" 
                            <?php echo isRadioChecked('other', $formData); ?>> Другой</label>
                        <label><input type="radio" name="gender" value="unspecified" 
                            <?php echo isRadioChecked('unspecified', $formData); ?>> Не указан</label>
                    </div>
                    <?php if (isset($errors['gender'])): ?>
                        <div class="error-message">
                            <?php echo safeOutput(getErrorMessage('gender')); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Языки программирования -->
                <div class="field-group <?php echo getErrorClass('languages'); ?>">
                    <label>💻 Любимые языки *</label>
                    <div class="input-wrapper">
                        <select name="fav_langs[]" id="fav_langs" multiple size="6" required>
                            <?php
                            $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                            foreach ($languages as $lang): ?>
                                <option value="<?php echo $lang; ?>" 
                                    <?php echo isLanguageSelected($lang, $formData); ?>>
                                    <?php echo $lang; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div style="font-size:0.7rem; color:#5b6e8c; margin-top:0.3rem;">
                            Удерживайте Ctrl (Cmd) для выбора нескольких языков
                        </div>
                        <?php if (isset($errors['languages'])): ?>
                            <div class="error-message">
                                <?php echo safeOutput(getErrorMessage('languages')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Биография -->
                <div class="field-group <?php echo getErrorClass('biography'); ?>">
                    <label for="bio">📝 Биография</label>
                    <div class="input-wrapper">
                        <textarea id="bio" name="bio" rows="4"><?php echo safeOutput($formData['biography'] ?? ''); ?></textarea>
                        <?php if (isset($errors['biography'])): ?>
                            <div class="error-message">
                                <?php echo safeOutput(getErrorMessage('biography')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Контракт -->
                <div class="field-group <?php echo getErrorClass('contract'); ?>">
                    <label>📑 Согласие</label>
                    <div class="input-wrapper checkbox-wrapper">
                        <input type="checkbox" id="contractCheck" name="contract_agreed" 
                            <?php echo isCheckboxChecked($formData); ?>>
                        <label for="contractCheck">Я ознакомлен(а) с условиями пользовательского соглашения *</label>
                    </div>
                    <?php if (isset($errors['contract'])): ?>
                        <div class="error-message">
                            <?php echo safeOutput(getErrorMessage('contract')); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Кнопка сохранения -->
                <div class="action-buttons">
                    <button type="submit" class="save-btn">💾 Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>