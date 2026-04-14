package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Quiz;
import edu.connexion3a36.services.QuizService;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;

import java.sql.SQLException;

public class ModifierQuizController {

    @FXML
    private Label idLabel;

    @FXML
    private TextField titreTF;

    @FXML
    private TextField statutTF;

    private final QuizService quizService = new QuizService();
    private Quiz selectedQuiz;

    public void setQuiz(Quiz quiz) {
        this.selectedQuiz = quiz;
        if (quiz != null) {
            idLabel.setText(String.valueOf(quiz.getIdQuiz()));
            titreTF.setText(quiz.getTitre());
            statutTF.setText(quiz.getStatut());
        }
    }

    @FXML
    public void modifierQuiz() {
        if (selectedQuiz == null) {
            ControllerUtils.showWarning("Selectionnez un quiz a modifier.");
            return;
        }

        if (!ControllerUtils.isTextValid(titreTF) || !ControllerUtils.isTextValid(statutTF)) {
            ControllerUtils.showError("Tous les champs doivent etre remplis et contenir plus de 2 caracteres.");
            return;
        }

        selectedQuiz.setTitre(titreTF.getText().trim());
        selectedQuiz.setStatut(statutTF.getText().trim());

        try {
            quizService.modifierQuiz(selectedQuiz);
            ControllerUtils.showInfo("Quiz modifie avec succes.");
            // after modification go back to list (preserve CSS)
            ControllerUtils.navigateTo(idLabel, "/quiz_list.fxml");
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de la modification du quiz : " + e.getMessage());
        }
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
