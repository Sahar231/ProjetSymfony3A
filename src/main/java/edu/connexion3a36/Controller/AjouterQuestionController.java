package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Question;
import edu.connexion3a36.services.QuestionService;
import javafx.fxml.FXML;
import javafx.scene.control.ChoiceBox;
import javafx.scene.control.TextArea;
import javafx.scene.control.TextField;

import java.sql.SQLException;

public class AjouterQuestionController {

    @FXML
    private TextArea questionTA;

    @FXML
    private TextField correctAnswerTF;

    @FXML
    private TextField scoreTF;

    @FXML
    private ChoiceBox<String> typeChoice;

    private final QuestionService questionService = new QuestionService();

    @FXML
    public void initialize() {
        typeChoice.getItems().addAll("Choix Multiple", "Vrai/Faux", "Directe");
    }

    @FXML
    public void ajouterQuestion() {
        if (!ControllerUtils.isTextValid(questionTA) || !ControllerUtils.isTextValid(correctAnswerTF)) {
            ControllerUtils.showError("Le texte et la réponse correcte doivent être renseignés et contenir au moins 2 caractères.");
            return;
        }

        if (!ControllerUtils.isDouble(scoreTF)) {
            ControllerUtils.showError("Le score doit être un nombre valide.");
            return;
        }

        String type = typeChoice.getValue();
        if (type == null || type.isEmpty()) {
            ControllerUtils.showError("Le type de question doit être sélectionné.");
            return;
        }

        double score = Double.parseDouble(scoreTF.getText().trim());
        Question question = new Question(questionTA.getText().trim(), correctAnswerTF.getText().trim(), score, type);

        try {
            questionService.ajouterQuestion(question);
            ControllerUtils.showInfo("Question ajoutée avec succès.");
            // Retour à la liste avec CSS conservé
            ControllerUtils.navigateTo(questionTA, "/question_list.fxml");
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de l'ajout de la question : " + e.getMessage());
        }
    }

    private void clearFields() {
        questionTA.clear();
        correctAnswerTF.clear();
        scoreTF.clear();
        typeChoice.setValue(null);
    }

    @FXML
    private void handleBack(javafx.event.ActionEvent event) {
        try {
            javafx.fxml.FXMLLoader loader = new javafx.fxml.FXMLLoader(getClass().getResource("/home.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.scene.Node source = (javafx.scene.Node) event.getSource();
            javafx.stage.Stage stage = (javafx.stage.Stage) source.getScene().getWindow();
            javafx.scene.Scene scene = stage.getScene();
            if (scene == null) {
                javafx.scene.Scene newScene = new javafx.scene.Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (java.io.IOException e) {
            ControllerUtils.showError("Impossible de retourner à l'accueil: " + e.getMessage());
        }
    }
}
